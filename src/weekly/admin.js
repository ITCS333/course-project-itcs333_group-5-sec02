/*
  Requirement: Make the "Manage Weekly Breakdown" page interactive.

  Instructions:
  1. Link this file to `admin.html` using:
     <script src="admin.js" defer></script> ---done
  
  2. In `admin.html`, add an `id="weeks-tbody"` to the <tbody> element
     inside your `weeks-table`.
  
  3. Implement the TODOs below.
*/

// --- Global Data Store ---
// This will hold the weekly data loaded from the JSON file.
let weeks = [];

// --- Element Selections ---
// TODO: Select the week form ('#week-form').

// TODO: Select the weeks table body ('#weeks-tbody').
const form = document.getElementById("week-form");
const tbody = document.getElementById("weeks-tbody");
const titleInput = document.getElementById("week-title");
const startDateInput = document.getElementById("week-start-date");
const descInput = document.getElementById("week-description");
const linksInput = document.getElementById("week-links");

// --- Functions ---

/**
 * TODO: Implement the createWeekRow function.
 * It takes one week object {id, title, description}.
 * It should return a <tr> element with the following <td>s:
 * 1. A <td> for the `title`.
 * 2. A <td> for the `description`.
 * 3. A <td> containing two buttons:
 * - An "Edit" button with class "edit-btn" and `data-id="${id}"`.
 * - A "Delete" button with class "delete-btn" and `data-id="${id}"`.
 */
function createWeekRow(week) {
  // ... your implementation here ...
  // 
const tr = document.createElement("tr");

  const tdTitle = document.createElement("td");
  tdTitle.textContent = week.title;

  const tdDesc = document.createElement("td");
  tdDesc.textContent = week.description || "No description";

  const tdActions = document.createElement("td");

  const editBtn = document.createElement("button");
  editBtn.textContent = "Edit";
  editBtn.className = "btn btn-outline-dark btn-sm me-2 edit-btn";
  editBtn.dataset.id = week.id;

 const deleteBtn = document.createElement("button");
  deleteBtn.textContent = "Delete";
  deleteBtn.className = "btn btn-outline-danger btn-sm delete-btn";
  deleteBtn.dataset.id = week.id;

  tdActions.appendChild(editBtn);
  tdActions.appendChild(deleteBtn);

  tr.appendChild(tdTitle);
  tr.appendChild(tdDesc);
  tr.appendChild(tdActions);

  return tr;
}

/**
 * TODO: Implement the renderTable function.
 * It should:
 * 1. Clear the `weeksTableBody`.
 * 2. Loop through the global `weeks` array.
 * 3. For each week, call `createWeekRow()`, and
 * append the resulting <tr> to `weeksTableBody`.
 */
function renderTable() {
  // ... your implementation here ...
 tbody.innerHTML = "";
  weeks.forEach(week => {
    tbody.appendChild(createWeekRow(week));
  });
}

/**
 * TODO: Implement the handleAddWeek function.
 * This is the event handler for the form's 'submit' event.
 * It should:
 * 1. Prevent the form's default submission.
 * 2. Get the values from the title, start date, and description inputs.
 * 3. Get the value from the 'week-links' textarea. Split this value
 * by newlines (`\n`) to create an array of link strings.
 * 4. Create a new week object with a unique ID (e.g., `id: \`week_${Date.now()}\``).
 * 5. Add this new week object to the global `weeks` array (in-memory only).
 * 6. Call `renderTable()` to refresh the list.
 * 7. Reset the form.
 */
function handleAddWeek(event) {
  // ... your implementation here ...
  event.preventDefault();

  const title = titleInput.value.trim();
  const startDate = startDateInput.value;
  const description = descInput.value.trim();

  if (!title || !startDate){
    alert("Title and start date are required!");
        return;
  }

  const links = linksInput.value
    .split("\n")
    .map(l => l.trim())
    .filter(l => l !== "");

  const newWeek = {
    id: `week_${Date.now()}`,
    title,
    startDate,
    description,
    links
  };

weeks.push(newWeek);
renderTable();
form.reset();
    }


/**
 * TODO: Implement the handleTableClick function.
 * This is an event listener on the `weeksTableBody` (for delegation).
 * It should:
 * 1. Check if the clicked element (`event.target`) has the class "delete-btn".
 * 2. If it does, get the `data-id` attribute from the button.
 * 3. Update the global `weeks` array by filtering out the week
 * with the matching ID (in-memory only).
 * 4. Call `renderTable()` to refresh the list.
 */
function handleTableClick(event) {
  // ... your implementation here ...
  const id = event.target.dataset.id;
if (!id) return;
  // DELETE
  if (event.target.classList.contains("delete-btn")) {
    if (confirm("Are you sure you want to delete this week?")) {
    weeks = weeks.filter(w => w.id !== id);
    renderTable();
  }
  }
  if (event.target.classList.contains("edit-btn")) {
    const week = weeks.find(w => w.id === id);
    if (!week) return;
titleInput.value = week.title;
    startDateInput.value = week.startDate;
    descInput.value = week.description || "";
    linksInput.value = (week.links || []).join("\n");

    // remove old version, will be replaced on submit
    weeks = weeks.filter(w => w.id !== id);
    renderTable();
  }
}

/**
 * TODO: Implement the loadAndInitialize function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'weeks.json'.
 * 2. Parse the JSON response and store the result in the global `weeks` array.
 * 3. Call `renderTable()` to populate the table for the first time.
 * 4. Add the 'submit' event listener to `weekForm` (calls `handleAddWeek`).
 * 5. Add the 'click' event listener to `weeksTableBody` (calls `handleTableClick`).
 */
async function loadAndInitialize() {
  // ... your implementation here ...
  try {
    const response = await fetch("../data/weeks.json");
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
     weeks = await response.json();
  } catch (error) {
    console.error("Error loading weeks:", error);
    weeks = [];
    tbody.innerHTML = `<tr><td colspan="3" class="text-danger">Failed to load weeks: ${error.message}</td></tr>`;
  }

  renderTable();
  form.addEventListener("submit", handleAddWeek);
  tbody.addEventListener("click", handleTableClick);
}

// --- Initial Page Load ---
// Call the main async function to start the application.
loadAndInitialize();
