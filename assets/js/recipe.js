let recipeCleanupFns = [];

export function initRecipeDetail() {
  recipeCleanupFns.forEach((fn) => fn());
  recipeCleanupFns = [];

  const favBtn = document.querySelector('.recipe-detail-fav-btn');
  if (favBtn) {
    const onFav = async () => {
      const url = favBtn.dataset.favoriteUrl;
      if (!url) return;
      try {
        const res = await fetch(url, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (res.status === 401 || res.status === 403) {
          window.location.href = '/login';
          return;
        }
        if (!res.ok) return;
        const data = await res.json();
        favBtn.classList.toggle('is-favorite', data.favorited);
        const icon = favBtn.querySelector('i');
        const label = favBtn.querySelector('.recipe-detail-fav-label');
        if (icon) icon.className = data.favorited ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
        if (label) label.textContent = data.favorited ? 'Saved' : 'Save';
      } catch {
        /* ignore */
      }
    };
    favBtn.addEventListener('click', onFav);
    recipeCleanupFns.push(() => favBtn.removeEventListener('click', onFav));
  }

  const ingredientItems = document.querySelectorAll('.recipe-ingredients-list li');
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
