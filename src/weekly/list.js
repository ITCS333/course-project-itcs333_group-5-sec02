/*
  Requirement: Populate the "Weekly Course Breakdown" list page.

  Instructions:
  1. Link this file to `list.html` using:
     <script src="list.js" defer></script>

  2. In `list.html`, add an `id="week-list-section"` to the
     <section> element that will contain the weekly articles.

  3. Implement the TODOs below.
*/

// --- Element Selections ---
// TODO: Select the section for the week list ('#week-list-section').
const weekListSection = document.getElementById("week-list-section");
// --- Functions ---

/**
 * TODO: Implement the createWeekArticle function.
 * It takes one week object {id, title, startDate, description}.
 * It should return an <article> element matching the structure in `list.html`.
 * - The "View Details & Discussion" link's `href` MUST be set to `details.html?id=${id}`.
 * (This is how the detail page will know which week to load).
 */
function createWeekArticle(week) {
  // ... your implementation here ...
  const article = document.createElement("article");
  article.className = "card p-4 mb-4 shadow-sm";
  
  const h2 = document.createElement("h2");
   h2.className = "h4 fw-bold mb-3";
   h2.textContent = week.title || "Untitled Week";
 

  const startP = document.createElement("p");
    startP.className = "text-muted mb-2";
    startP.textContent = `Starts on: ${week.startDate || "Date not set"}`;
  
  const descP = document.createElement("p");
  descP.className = "mb-3";
   descP.textContent = week.description || "No description available";
  

  const a = document.createElement("a");
  a.href = `details.html?id=${week.id}`;
  a.className = "btn btn-outline-primary";
  a.textContent = "View Details & Discussion";
  
    article.appendChild(h2);
    article.appendChild(startP);
    article.appendChild(descP);
    article.appendChild(a);
  return article;
}

/**
 * TODO: Implement the loadWeeks function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'weeks.json'.
 * 2. Parse the JSON response into an array.
 * 3. Clear any existing content from `listSection`.
 * 4. Loop through the weeks array. For each week:
 * - Call `createWeekArticle()`.
 * - Append the returned <article> element to `listSection`.
 */
async function loadWeeks() {
  // ... your implementation here ...
   try {
    const response = await fetch('weeks.json');
    if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const weeks = await response.json();
        
        weekListSection.innerHTML = "";
    if (weeks.length === 0) {
            const emptyMsg = document.createElement("div");
            emptyMsg.className = "alert alert-info";
            emptyMsg.textContent = "No weeks available yet.";
            weekListSection.appendChild(emptyMsg);
            return;
        }
        
        weeks.forEach(week => {
            const article = createWeekArticle(week);
            weekListSection.appendChild(article);
        });
         } catch (error) {
        console.error("Error loading weeks:", error);
        
        const errorMsg = document.createElement("div");
        errorMsg.className = "alert alert-danger";
        errorMsg.textContent = `Failed to load weeks: ${error.message}`;
        weekListSection.appendChild(errorMsg);
    }


}

// --- Initial Page Load ---
// Call the function to populate the page.
loadWeeks();
