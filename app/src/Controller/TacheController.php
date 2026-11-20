<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\HttpException;
use App\Core\Texte;
use App\Model\FiltreTaches;
use App\Model\Tache;
use DateTime;
use PDOException;

final class TacheController extends Controller
{
    public function liste(): void
    {
        $u = $this->utilisateur();
        $filtre = FiltreTaches::depuisRequete($this->app->request);

        $this->app->vue->afficher('tache/liste', 'Liste des tâches', [
            'taches'    => $this->app->taches->lister($u, $filtre),
            'projets'   => $this->app->projets->visibles($u),
            'peutCreer' => (bool) $this->app->projets->gerables($u),
            'f'         => $filtre,
        ]);
    }

    public function detail(int $id): void
    {
        $u = $this->utilisateur();
        $t = $this->tacheAccessible($id, $u);
        $perm = $this->app->permissions;

        $this->app->vue->afficher('tache/detail', $t->nom, [
            't'            => $t,
            'attribues'    => $this->app->taches->attribues($id),
            'commentaires' => $this->app->commentaires->arbreDeLaTache($id),
            'nbCommentaires' => $this->app->commentaires->total($id),
            'historique'   => $this->app->historique->duneTache($id),
            'u'            => $u,
            'gerer'        => $perm->peutGerer($u, $t->idProjet),
            'changerEtat'  => $perm->peutChangerEtat($u, $t),
            'peutCommenter' => $perm->peutCommenter($u, $t),
        ]);
    }

    /** Création (sans id) ou modification (avec id). */
    public function formulaire(?int $id = null): void
    {
        $u = $this->utilisateur();
        [$donnees, $attribues] = $this->charger($id, $u);
        $this->afficherFormulaire($id, $donnees, $attribues, []);
    }

    public function enregistrer(?int $id = null): void
    {
        $u = $this->utilisateur();
        $this->verifierCsrf();
        $req = $this->app->request;
        [$donnees, $attribuesActuels] = $this->charger($id, $u);
        $gerables = $this->app->projets->gerables($u);
        $erreurs = [];

        if (!$id) {
            $donnees['id_projet'] = $req->postInt('id_projet') ?? 0;
            if (!in_array($donnees['id_projet'], array_map('intval', array_column($gerables, 'id')), true)) {
                $erreurs[] = 'Projet invalide.';
            }
        }
        $donnees['nom']         = trim($req->post('nom'));
        $donnees['description'] = $req->postMultiligne('description');
        $donnees['etat']        = $req->post('etat');
        $debut = $this->dateValide(trim($req->post('dateDebut')));
        $fin   = $this->dateValide(trim($req->post('dateFin')));
        $donnees['dateDebut'] = (string) $debut;
        $donnees['dateFin']   = (string) $fin;

        if ($donnees['nom'] === '' || mb_strlen($donnees['nom']) > 150) {
            $erreurs[] = 'Titre : 1 à 150 caractères.';
        }
        if (Texte::melangeAlphabets($donnees['nom'])) {
            $erreurs[] = 'Titre : un même mot ne peut pas mélanger alphabet latin et cyrillique/grec.';
        }
        if (mb_strlen($donnees['description']) > 5000) {
            $erreurs[] = 'Description : 5000 caractères maximum.';
        }
        if (!isset(Tache::ETATS[$donnees['etat']])) {
            $erreurs[] = 'État invalide.';
        }
        if ($debut === false || $fin === false) {
            $erreurs[] = 'Date invalide.';
        } elseif ($debut && $fin && $fin < $debut) {
            $erreurs[] = 'La date de fin doit être postérieure à la date de début.';
        }

        // Les utilisateurs attribués doivent être membres du projet
        $choisis = array_values(array_unique(array_map('intval', $req->postTableau('utilisateurs'))));
        if (!$erreurs && array_diff($choisis, $this->app->projets->idsMembres($donnees['id_projet']))) {
            $erreurs[] = 'Un utilisateur attribué ne fait pas partie du projet.';
        }

        if (!$erreurs) {
            $pdo = $this->app->pdo;
            try {
                $pdo->beginTransaction();
                $desc = $donnees['description'] ?: null;
                if ($id) {
                    $avant = $this->app->taches->trouver($id);
                    $this->app->taches->modifier($id, $donnees['nom'], $desc, $debut, $fin, $donnees['etat']);
                    $changements = $this->differences($avant, $donnees);
                    if ($changements) {
                        $this->app->historique->tache($id, $u->id, 'Modification : ' . implode(' ; ', $changements));
                    }
                } else {
                    $id = $this->app->taches->creer($donnees['id_projet'], $donnees['nom'], $desc, $debut, $fin, $donnees['etat']);
                    $this->app->historique->tache($id, $u->id, 'Création de la tâche');
                }
                if ($this->app->taches->synchroniserAttributions($id, $choisis)) {
                    $this->app->historique->tache($id, $u->id, 'Attribution modifiée (' . count($choisis) . ' utilisateur(s))');
                }
                $pdo->commit();
                $this->flash('ok', 'Tâche enregistrée.');
                $this->rediriger('/taches/' . $id);
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log($e->getMessage());
                $erreurs[] = 'Enregistrement impossible.';
            }
        }
        $this->afficherFormulaire($id, $donnees, $choisis, $erreurs);
    }

    public function supprimer(int $id): void
    {
        $this->verifierCsrf();
        $u = $this->utilisateur();
        $t = $this->tacheAccessible($id, $u);
        if (!$this->app->permissions->peutGerer($u, $t->idProjet)) {
            throw HttpException::interdit('Seul un admin ou le chef de projet peut supprimer une tâche.');
        }
        $pdo = $this->app->pdo;
        try {
            $pdo->beginTransaction();
            $this->app->taches->supprimer($id); // commentaires, réponses et attributions partent en cascade
            // L'historique de la tâche part avec elle : la trace reste dans l'historique du projet
            $this->app->historique->projet($t->idProjet, $u->id, 'Suppression de la tâche « ' . $t->nom . ' »');
            $pdo->commit();
            $this->flash('ok', 'Tâche supprimée.');
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log($e->getMessage());
            $this->flash('erreur', 'Suppression impossible.');
        }
        $this->rediriger('/taches');
    }

    public function changerEtat(int $id): void
    {
        $this->verifierCsrf();
        $u = $this->utilisateur();
        $etat = $this->app->request->post('etat');
        if (!isset(Tache::ETATS[$etat])) {
            throw HttpException::interdit('Requête invalide.');
        }
        $t = $this->tacheAccessible($id, $u);
        if (!$this->app->permissions->peutChangerEtat($u, $t)) {
            throw HttpException::interdit("Seul un admin, le chef de projet ou un utilisateur attribué peut changer l'état.");
        }
        if ($t->etat !== $etat) {
            $this->app->taches->changerEtat($id, $etat);
            $this->app->historique->tache($id, $u->id, 'État : « ' . Tache::ETATS[$t->etat] . ' » → « ' . Tache::ETATS[$etat] . ' »');
            $this->flash('ok', 'État mis à jour.');
        }
        $this->rediriger('/taches/' . $id);
    }

    /** @return array{array, int[]} valeurs du formulaire + ids des attribués */
    private function charger(?int $id, $u): array
    {
        $donnees = ['id_projet' => $this->app->request->getInt('projet') ?? 0, 'projet_nom' => '', 'nom' => '',
                    'description' => '', 'dateDebut' => '', 'dateFin' => '', 'etat' => 'a_faire'];
        if ($id) {
            $t = $this->tacheAccessible($id, $u);
            if (!$this->app->permissions->peutGerer($u, $t->idProjet)) {
                throw HttpException::interdit('Seul un admin ou le chef de projet peut modifier cette tâche.');
            }
            return [[
                'id_projet' => $t->idProjet, 'projet_nom' => $t->projetNom, 'nom' => $t->nom,
                'description' => (string) $t->description, 'dateDebut' => (string) $t->dateDebut,
                'dateFin' => (string) $t->dateFin, 'etat' => $t->etat,
            ], $this->app->taches->idsAttribues($id)];
        }
        if (!$this->app->projets->gerables($u)) {
            throw HttpException::interdit('Seul un admin ou un chef de projet peut créer une tâche.');
        }
        return [$donnees, []];
    }

    private function afficherFormulaire(?int $id, array $donnees, array $attribues, array $erreurs): void
    {
        $gerables = $this->app->projets->gerables($this->utilisateur());
        $ids = array_map('intval', array_column($gerables, 'id'));
        $courant = (int) $donnees['id_projet'];
        if (!$id && !in_array($courant, $ids, true)) {
            $courant = $ids[0] ?? 0;
        }
        $this->app->vue->afficher('tache/formulaire', $id ? 'Modifier la tâche' : 'Nouvelle tâche', [
            'id' => $id, 'tache' => $donnees, 'gerables' => $gerables, 'attribues' => $attribues,
            'membresParProjet' => $this->app->projets->membresParProjet($ids),
            'projetCourant' => $courant, 'erreurs' => $erreurs,
        ]);
    }

    /** @return string[] */
    private function differences(Tache $avant, array $apres): array
    {
        $champs = ['nom' => ['titre', $avant->nom], 'description' => ['description', (string) $avant->description],
                   'dateDebut' => ['date de début', (string) $avant->dateDebut], 'dateFin' => ['date de fin', (string) $avant->dateFin],
                   'etat' => ['état', $avant->etat]];
        $res = [];
        foreach ($champs as $cle => [$lib, $ancien]) {
            if ($ancien !== (string) $apres[$cle]) {
                $res[] = $cle === 'description' ? 'description modifiée' : "$lib : « $ancien » → « {$apres[$cle]} »";
            }
        }
        return $res;
    }

    /** Date AAAA-MM-JJ valide ; '' => null ; sinon false. */
    private function dateValide(string $d): string|false|null
    {
        if ($d === '') {
            return null;
        }
        $dt = DateTime::createFromFormat('!Y-m-d', $d);
        return ($dt && $dt->format('Y-m-d') === $d) ? $d : false;
    }
}
