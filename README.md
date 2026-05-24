# TuniCuisine 🇹🇳

Discover authentic Tunisian recipes from every corner of Tunisia – from the streets of Tunis to the shores of Djerba.  
**TuniCuisine** is a university web project that will present traditional Tunisian meals, their ingredients and preparation steps, in a modern and simple website inspired by local colors and symbols (chachia, Tunisian doors, mosaics, couscous pots, etc.).

>  **Status:** Symfony app with homepage (recipes), regions, ingredients, cooking tips, about, and Ask the Chef.

---

## Project Idea

TuniCuisine will be a **meal recipe web application** focused on Tunisian food.

Main goals:

- Show **famous dishes** from different Tunisian regions
- Display **ingredients** and a clear list of **steps** for each recipe
- Allow users to **search** for recipes by name, ingredient or region
- Later, add a small **chatbot** to help users choose or understand recipes
- Keep a **Tunisian-style design** with warm colors and traditional decorative elements

At this moment, we are mainly working on:

- Designing the basic pages (home, recipes list, recipe details)
- Choosing colors, fonts and layout
- Planning how data (recipes, ingredients, regions) will be organized

---

##  Planned Tech Stack

We will build the project step by step. Technologies will be added gradually during the course.

### Phase 1 – Frontend (Current Step)

- **HTML5** – Page structure (headers, sections, cards, lists…)
- **CSS3 + Bootstrap** – Basic styling and responsive layout
- **JavaScript** – Simple interactions (show/hide sections, basic filtering)

### Phase 2 – Backend Basics

- **PHP** – First server-side scripts to:
  - Organize common parts (header, footer, navbar)
  - Prepare simple routing between pages

### Phase 3 – Database (Planned)

- Store recipes, ingredients and regions in tables
- Connect PHP pages to the database to display real data

### Phase 4 – Symfony Framework (Planned)

- **Symfony** – Turn the project into a real MVC application:
  - Controllers, routes, Twig templates
  - Doctrine ORM for database access

---

##  Planned Pages

These pages are planned but not all are implemented yet:

- **Home**
  - Hero section with Tunisian background image
  - Search bar: `Search recipes, ingredients…`
  - Some highlighted recipes / regions

- **Recipes**
  - List of recipes in cards
  - Simple filters (by category or region)

- **Recipe Details**
  - Name, photo (later)
  - Ingredients list
  - Steps to prepare the dish

- **Regions**
  - Simple list of Tunisian regions with a few famous dishes for each (later)

- **About / Team**
  - Short description of the project and the students

- **Ask the Chef (Future)**
  - Page reserved for a future chatbot assistant

---

##  Team

TuniCuisine is developed by RT2 students as part of a web development course.

- Ahmed Ben Salah  
- Mohamed Aziz Abbes  
- Yassin Amri  
- Salem Bouchahwa  
- Anas Abd Elmalek Cherif  

Planned roles:

- Frontend (HTML/CSS/JS/Bootstrap)
- Backend & PHP / Symfony
- Database & data modeling
---

## Run locally

Requirements: **PHP 8.4+**, Composer, SQLite (or PostgreSQL via Docker Compose).

```bash
composer install
cp .env.dev .env   # or use the included .env with SQLite
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
symfony server:start
# or: php -S localhost:8000 -t public
```

Open http://localhost:8000 — the **recipes page is the homepage**.

## Recipe images

Add photos under `public/images/recipes/` named by slug (lowercase, hyphens):

| Recipe | Filename |
|--------|----------|
| Mloukhia | `mloukhia.jpg` |
| Couscous Royal | `couscous-royal.jpg` |
| Brik à l'Oeuf | `brik-a-l-oeuf.jpg` |
| Lablabi | `lablabi.jpg` |
| Makroudh | `makroudh.jpg` |

## Project status

- Homepage: recipes grid with search, region filters, favorites (browser storage)
- Backend: Symfony 8, Doctrine, fixtures
- Pages: About, Regions, Ingredients, Cooking Tips (`/tips`), Ask Chef

---

> “Cooking is like love. It should be entered into with abandon or not at all.”  
> Welcome to **TuniCuisine** – a small student project with a big appetite for Tunisian flavors.
