let recipeCleanupFns = [];

export function initRecipeDetail() {
    recipeCleanupFns.forEach(fn => fn());
    recipeCleanupFns = [];

    const ingredientItems = document.querySelectorAll('.ingredient-item');
    if (!ingredientItems.length) return;

    ingredientItems.forEach(item => {
        const onClick = () => {
            item.classList.toggle('checked');
            const icon = item.querySelector('.ingredient-check i');
            if (icon) {
                icon.className = item.classList.contains('checked')
                    ? 'fa-solid fa-check'
                    : 'fa-regular fa-circle';
            }
        };
        item.addEventListener('click', onClick);
        recipeCleanupFns.push(() => item.removeEventListener('click', onClick));
    });
}
