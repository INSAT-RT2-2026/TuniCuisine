# TuniCuisine 🇹🇳

Discover authentic Tunisian recipes from every corner of Tunisia – from the bustling streets of Tunis to the coastal beauty of Djerba.  
**TuniCuisine** is a student-built web platform that showcases traditional meals, their ingredients, and preparation steps, wrapped in a modern UI inspired by Tunisian colors, handicrafts, and symbols (chachia, mosaics, doors of Tunis, etc.).

---

## 🍲 Project Description

TuniCuisine is a **meal recipe web application** focused on Tunisian gastronomy.  
It allows users to:

- Explore famous dishes from different **regions / governorates**
- View **ingredients, quantities, and cooking steps**
- Discover the **story and culture** behind each meal
- Search recipes by **name, ingredient, category, or region**
- (Future) Chat with an integrated **virtual chef chatbot** to get cooking help and suggestions

The design is inspired by authentic Tunisian elements:

- Color palette based on **red, beige, and sand tones**
- Decorative icons and illustrations such as **chachia**, traditional doors, couscous pots, harissa jars
- Section names and labels mixing **English and a touch of Arabic / French** when appropriate

---

## 🧭 Core Features

### 1. Recipes Explorer

- Browse a curated list of Tunisian dishes:
  - Couscous, Lablabi, Brik, Ojja, Kamounia, Kabkabou, Mloukhiya, etc.
- Each recipe page includes:
  - Short **description & origin**
  - **Ingredients list** with quantities
  - **Step-by-step preparation**
  - Preparation & cooking time
  - Difficulty level and **spice level**
  - Serving suggestions (e.g. with salad méchouia, bread, olives…)

### 2. Smart Search & Filters

- Global search bar: `Search recipes, ingredients…`
- Filters:
  - By **region**: Tunis, Sfax, Sousse, Kairouan, Bizerte, Gabès, Djerba, Nabeul, Monastir, …
  - By **category**: Main Dish, Appetizer, Dessert, Soup, Street Food, Drinks
- Combines filters (e.g. “Spicy street food from Sfax”)

### 3. Regions & Culture Page

A **“Regions”** section to travel through Tunisia via food:

- Map / list of regions with:
  - Short cultural description
  - Main local specialties
  - Visual hint (sea, desert, medina, etc.)

### 4. Ingredients Library

- Page listing **key Tunisian ingredients**:
  - Harissa, olive oil, haricot beans, spices, dried meat, seafood, dates, etc.
- For each ingredient:
  - Description and common uses
  - Example recipes using it

### 5. Cooking Tips & Blog

- Small articles / cards with:
  - How to cook couscous the traditional way
  - How to control spiciness
  - How to prepare a typical **Tunisian menu** for guests
- Can be extended into a **blog-like** experience.
---

## 🧱 Tech Stack & Project Phases

This project is developed progressively in **four main phases**, similar to a real-world web development workflow.

### Phase 1 – Frontend Foundation

- **HTML5** – Structure and semantic markup for all pages:
  - Home, Recipes, Regions, Ingredients, Cooking Tips, Contact / About
- **CSS3 + Bootstrap** – Styling and layout:
  - Responsive grid for cards and recipe lists
  - Navbar, buttons, filters, modals
  - Custom theming to match Tunisian colors and patterns
- **JavaScript (Vanilla)** – Client-side interactivity:
  - Dynamic filters and search
  - Show/hide recipe details
  - Smooth scrolling, small animations (hover effects, transitions)

### Phase 2 – Backend Integration

- **PHP** – Server-side logic:
  - Routing between main pages
  - Reusable layout components (header, footer, navbar)
  - Handling forms (contact form, feedback, future user accounts)
- Basic architecture to prepare for Symfony migration:
  - Separation between **views**, **controllers**, and **data layer (placeholder)**

### Phase 3 – Database Layer

- **PostgreSQL** – Relational database:
  - Tables for:
    - `recipes` (title, description, region, category, difficulty, times…)
    - `ingredients`
    - `recipe_ingredients` (pivot table)
    - (Optional) `users`, `favorites`, `comments`
  - CRUD operations for recipes and ingredients via PHP
- Data access layer to:
  - Fetch recipes by filters
  - Search by ingredient or name
  - Prepare clean entities for use in Symfony

### Phase 4 – MVC Framework with Symfony

- **Symfony** – Full MVC application:
  - Routing & controllers (Home, Recipes, Regions, Ingredients, Tips, API endpoints)
  - **Twig** templates for views (layouts, components, partials)
  - **Doctrine ORM** to map entities to PostgreSQL tables
  - Services for:
    - Search & filtering logic
    - Preparing data for the future chatbot API

---

## 📄 Main Pages & Navigation

Planned structure of the application:

- **Home / Landing Page**
  - Hero section with Tunisian background image and main search bar  
  - Highlighted regions and popular dishes  
  - Call-to-action buttons (Explore Recipes, Discover Regions)

- **Recipes Page**
  - Cards grid for all recipes
  - Filters (region, category, difficulty)
  - Pagination or “Load more” button

- **Single Recipe Page**
  - Large image, title, icons (time, difficulty, spice level)
  - Ingredients & steps
  - Related recipes section

- **Regions Page**
  - Region selector (tabs or map)
  - For each region: description + list of signature dishes

- **Ingredients Page**
  - Alphabetical list or grid of ingredients
  - Clicking an ingredient shows its description and related recipes

- **Cooking Tips / Blog Page**
  - Articles with quick tips, “Did you know?” cultural notes

- **About / Team Page**
  - Project story
  - Team members & roles

- **(Future) Ask Chef / Chatbot Page**
  - Interface to chat with a virtual Tunisian chef
  - Integrated with recipe database

---

## 🎨 UI & Tunisian Style

To keep a strong Tunisian identity:

- **Colors**
  - Main: `#C8102E` (Tunisian red), `#F5E3C4` (sand), dark brown/black for text
- **Typography**
  - Clean Latin font + optional Arabic-inspired accent font for titles
- **Icons & Decorations**
  - Chachia icon for logo or favicon
  - Traditional doors silhouettes, mosaics as subtle backgrounds
  - Small illustrations for categories (couscous pot, tajine, cup of mint tea)

---

## 👥 Project Methodology

TuniCuisine is developed by a team of students as part of a university web development course.

We follow **industry-inspired practices**:

- **Version Control**
  - Git & GitHub for source code management
  - Branches for each feature, regular commits
- **Workflow**
  - Git branching strategy (feature branches, pull requests, code reviews)
  - Issues or task board to track features and bugs
- **Collaboration**
  - Weekly meetings to plan and review progress
  - Shared decisions on UI/UX and data model

---

## 🚀 Future Enhancements

- Full **user accounts** with:
  - Favorites list
  - Personal notes on recipes
- **Rating & comments** on recipes
- **Multilingual support** (FR / AR / EN)
- Mobile-first optimizations and PWA features (offline access to saved recipes)

---

## 🧪 How to Run the Project (Planned)

> Note: The exact commands may evolve once the Symfony structure is finalized.

1. **Clone the repository**
   ```bash
   git clone https://github.com/INSAT-RT2-2026/TuniCuisine.git
   cd TuniCuisine
   ```
## 👨‍🍳 Contributors

- Team of RT2 students – Web Development Project

- Ahmed Ben Salah
- Mohamed Aziz Abbes
- Yassin Amri
- Salem Bouchahwa
- Anas Abd Elmalek Cherif
- Roles may include:
  - Frontend (HTML/CSS/JS/Bootstrap)
  - Backend & Symfony
  - Database & data modeling
  - UI/UX & content (recipes, regions, descriptions)


---

> “Cooking is like love. It should be entered into with abandon or not at all.”  
> Welcome to **TuniCuisine**, where every line of code smells like freshly cooked couscous.
