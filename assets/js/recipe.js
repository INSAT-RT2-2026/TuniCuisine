let recipeCleanupFns = [];

export function initRecipeDetail() {
  recipeCleanupFns.forEach((fn) => fn());
  recipeCleanupFns = [];

  const ingredientItems = document.querySelectorAll('.recipe-ingredients-list li');
  if (!ingredientItems.length) return;

  ingredientItems.forEach((item) => {
    item.style.cursor = 'pointer';
    const onClick = () => {
      item.classList.toggle('checked');
      item.style.opacity = item.classList.contains('checked') ? '0.55' : '1';
    };
    item.addEventListener('click', onClick);
    recipeCleanupFns.push(() => item.removeEventListener('click', onClick));
  });
}
