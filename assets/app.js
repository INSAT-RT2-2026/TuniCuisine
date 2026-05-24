import './stimulus_bootstrap.js';
import './controllers/csrf_protection_controller.js';
import './styles/style.css';
import './styles/styles.css';
import './styles/auth-admin.css';
import { initNav } from './js/nav.js';

import { initTips } from './js/tips.js';
import { initIngredients } from './js/ingredients.js';
import { initAskChef } from './js/ask_chef.js';
import { initRecipeDetail } from './js/recipe.js';
import { initRecipes } from './js/recipes.js';
import { initRegions } from './js/regions.js';

function initPage() {
    initNav();
    if (document.querySelector('.philosophy-section') || document.querySelector('.masterclass-container')) {
        initTips();
    }
    if (document.getElementById('cardsGrid')) {
        initIngredients();
    }
    if (document.querySelector('.ask-chef-section')) {
        initAskChef();
    }
    if (document.querySelector('.regions-page')) {
        initRegions();
    }
    if (document.querySelector('.recipes-page')) {
        initRecipes();
    }
    if (document.querySelector('.recipe-detail-page')) {
        initRecipeDetail();
    }
}

// Turbo fires this on initial load AND every Turbo visit
document.addEventListener('turbo:load', initPage);
// Also run immediately in case turbo:load already fired before this module executed
initPage();
