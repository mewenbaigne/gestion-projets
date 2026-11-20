// Script externe (la CSP interdit tout JavaScript en ligne)

// Confirmation avant les actions destructrices : <form data-confirm="Message ?">
document.addEventListener('submit', (e) => {
    const message = e.target.dataset ? e.target.dataset.confirm : null;
    if (message && !confirm(message)) {
        e.preventDefault();
    }
});

// Formulaire de tâche : n'affiche que les membres du projet sélectionné (la vérification réelle est côté serveur)
const select = document.getElementById('id_projet');
if (select) {
    const maj = () => document.querySelectorAll('.membres-projet').forEach((bloc) => {
        const actif = bloc.dataset.projet === select.value;
        bloc.hidden = !actif;
        bloc.querySelectorAll('input').forEach((i) => { i.disabled = !actif; });
    });
    select.addEventListener('change', maj);
}
