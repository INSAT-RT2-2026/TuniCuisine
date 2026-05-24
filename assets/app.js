import './stimulus_bootstrap.js';
import './styles/style.css';
import './styles/styles.css';

import { initTips } from './js/tips.js';
import { initIngredients } from './js/ingredients.js';
import { initAskChef } from './js/ask_chef.js';
import { initRecipeDetail } from './js/recipe.js';

function initPage() {
    if (document.querySelector('.philosophy-section') || document.querySelector('.masterclass-container')) {
        initTips();
    }
    if (document.getElementById('cardsGrid')) {
        initIngredients();
    }
    if (document.querySelector('.ask-chef-section')) {
        initAskChef();
    }
    if (document.querySelector('.recipe-detail-section')) {
        initRecipeDetail();
    }
}

// Turbo fires this on initial load AND every Turbo visit
document.addEventListener('turbo:load', initPage);
// Also run immediately in case turbo:load already fired before this module executed
initPage();
