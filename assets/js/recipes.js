const FAVORITES_KEY = 'tunicuisine_favorites';

let recipesCleanupFns = [];

function getFavorites() {
  try {
    const raw = localStorage.getItem(FAVORITES_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

function setFavorites(ids) {
  localStorage.setItem(FAVORITES_KEY, JSON.stringify(ids));
}

function toggleFavorite(id) {
  const ids = getFavorites();
  const idx = ids.indexOf(id);
  if (idx >= 0) {
    ids.splice(idx, 1);
  } else {
    ids.push(id);
  }
  setFavorites(ids);
  return ids;
}

function updateFavoriteButtons() {
  const ids = getFavorites();
  document.querySelectorAll('.recipe-fav-btn').forEach((btn) => {
    const isFav = ids.includes(btn.dataset.recipeId);
    btn.classList.toggle('is-favorite', isFav);
    const icon = btn.querySelector('i');
    if (icon) {
      icon.className = isFav ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
    }
  });

  const favoritesSection = document.getElementById('recipes-favorites-section');
  const favGrid = document.getElementById('favorites-grid');
  if (!favoritesSection || !favGrid) return;

  favGrid.innerHTML = '';
  const sourceCards = document.querySelectorAll('.recipes-main > .container > .recipes-grid .recipe-card-wrap');
  let count = 0;

  sourceCards.forEach((wrap) => {
    if (!ids.includes(wrap.dataset.recipeId)) return;
    count += 1;
    const clone = wrap.cloneNode(true);
    favGrid.appendChild(clone);
  });

  favoritesSection.hidden = count === 0;
  const countEl = document.getElementById('favorites-count');
  if (countEl) countEl.textContent = String(count);

  rebindFavoriteButtons(favGrid);
}

function rebindFavoriteButtons(root = document) {
  root.querySelectorAll('.recipe-fav-btn').forEach((btn) => {
    if (btn.dataset.bound) return;
    btn.dataset.bound = '1';
    const handler = (e) => {
      e.preventDefault();
      e.stopPropagation();
      toggleFavorite(btn.dataset.recipeId);
      updateFavoriteButtons();
    };
    btn.addEventListener('click', handler);
    recipesCleanupFns.push(() => {
      btn.removeEventListener('click', handler);
      delete btn.dataset.bound;
    });
  });
}

export function initRecipes() {
  recipesCleanupFns.forEach((fn) => fn());
  recipesCleanupFns = [];

  if (!document.querySelector('.recipes-page')) return;

  rebindFavoriteButtons();
  updateFavoriteButtons();

  const searchInput = document.querySelector('.recipes-search-input');
  const form = document.querySelector('.recipes-search-form');
  if (searchInput && form) {
    let debounce;
    const onInput = () => {
      clearTimeout(debounce);
      debounce = setTimeout(() => {
        if (searchInput.value.trim() === '' && !window.location.search.includes('q=')) {
          return;
        }
        form.requestSubmit();
      }, 600);
    };
    searchInput.addEventListener('input', onInput);
    recipesCleanupFns.push(() => searchInput.removeEventListener('input', onInput));
  }
}
