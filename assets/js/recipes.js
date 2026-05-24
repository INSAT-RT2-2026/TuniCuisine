const FAVORITES_KEY = 'tunicuisine_favorites';

let recipesCleanupFns = [];

function getPageConfig() {
  const root = document.querySelector('.recipes-page');
  if (!root) {
    return { loggedIn: false, favoriteIds: [] };
  }
  let favoriteIds = [];
  try {
    favoriteIds = JSON.parse(root.dataset.favoriteIds || '[]');
  } catch {
    favoriteIds = [];
  }
  return {
    loggedIn: root.dataset.userLoggedIn === '1',
    favoriteIds: favoriteIds.map(String),
  };
}

function getFavoritesLocal() {
  try {
    const raw = localStorage.getItem(FAVORITES_KEY);
    return raw ? JSON.parse(raw).map(String) : [];
  } catch {
    return [];
  }
}

function setFavoritesLocal(ids) {
  localStorage.setItem(FAVORITES_KEY, JSON.stringify(ids));
}

function getActiveFavoriteIds() {
  const { loggedIn, favoriteIds } = getPageConfig();
  return loggedIn ? [...favoriteIds] : getFavoritesLocal();
}

function setActiveFavoriteIds(ids) {
  const { loggedIn } = getPageConfig();
  if (!loggedIn) {
    setFavoritesLocal(ids);
  }
}

async function toggleFavoriteServer(recipeId) {
  const response = await fetch(`/recipe/${recipeId}/favorite`, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });

  if (response.status === 401 || response.status === 403) {
    window.location.href = '/login';
    return null;
  }

  if (!response.ok) {
    throw new Error('Could not update favorite');
  }

  return response.json();
}

function updateFavoriteButtons(favoriteIds) {
  const ids = favoriteIds ?? getActiveFavoriteIds();

  document.querySelectorAll('.recipe-fav-btn').forEach((btn) => {
    const id = String(btn.dataset.recipeId);
    const isFav = ids.includes(id);
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
    if (!ids.includes(String(wrap.dataset.recipeId))) return;
    count += 1;
    const clone = wrap.cloneNode(true);
    favGrid.appendChild(clone);
  });

  favoritesSection.hidden = count === 0;
  const countEl = document.getElementById('favorites-count');
  if (countEl) countEl.textContent = String(count);

  rebindFavoriteButtons(favGrid, ids);
}

function rebindFavoriteButtons(root = document, currentIds = null) {
  root.querySelectorAll('.recipe-fav-btn').forEach((btn) => {
    if (btn.dataset.bound) return;
    btn.dataset.bound = '1';

    const handler = async (e) => {
      e.preventDefault();
      e.stopPropagation();

      const recipeId = String(btn.dataset.recipeId);
      const { loggedIn } = getPageConfig();
      let ids = [...(currentIds ?? getActiveFavoriteIds())];

      if (loggedIn) {
        try {
          const data = await toggleFavoriteServer(recipeId);
          if (!data) return;
          if (data.favorited) {
            if (!ids.includes(recipeId)) ids.push(recipeId);
          } else {
            ids = ids.filter((id) => id !== recipeId);
          }
          const page = document.querySelector('.recipes-page');
          if (page) {
            page.dataset.favoriteIds = JSON.stringify(ids.map(Number));
          }
        } catch {
          return;
        }
      } else {
        const idx = ids.indexOf(recipeId);
        if (idx >= 0) {
          ids.splice(idx, 1);
        } else {
          ids.push(recipeId);
        }
        setActiveFavoriteIds(ids);
      }

      updateFavoriteButtons(ids);
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

  if (window.location.hash === '#favorites') {
    const section = document.getElementById('recipes-favorites-section');
    section?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

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
