(function () {
  "use strict";
  const searchInput= document.getElementById("searchInput");
  const resultCount= document.getElementById("resultCount");
  const catButtons= document.querySelectorAll(".cat-btn");
  const cardsGrid = document.getElementById("cardsGrid");

  let activeCategory="All";
  function filterCards(){
    const cards= cardsGrid.querySelectorAll(".card");
    const query= searchInput.value.trim().toLowerCase();
    let visible= 0;
    const existing= cardsGrid.querySelector(".empty-state");
    if (existing) existing.remove();
    cards.forEach((card) => {
      const name = card.dataset.name || "";
      const category = card.dataset.category || "";

      const matchesCat = activeCategory === "All" || category === activeCategory;
      const matchesSearch = query === "" || name.includes(query) || category.toLowerCase().includes(query);

      if (matchesCat && matchesSearch) {
        card.classList.remove("hidden");
        visible++;
      } else {
        card.classList.add("hidden");
      }
    });

    resultCount.textContent =
      visible === 0
        ? "No ingredients found"
        : `${visible} ingredient${visible > 1 ? "s" : ""} found`;


    if (visible === 0) {
      const msg = document.createElement("p");
      msg.className = "empty-state";
      msg.textContent = "No ingredients match your search…";
      cardsGrid.appendChild(msg);
    }
  }

  catButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      catButtons.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      activeCategory = btn.dataset.cat;
      filterCards();
    });
  });

  searchInput.addEventListener("input", filterCards);
  searchInput.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      searchInput.value = "";
      filterCards();
    }
  });
  filterCards();
})();