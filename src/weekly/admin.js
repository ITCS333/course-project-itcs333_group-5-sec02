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
let form = document.getElementById("week-form");
let tbody = document.getElementById("weeks-tbody");

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
let tr=document.createElement("tr");

let tdtitle=document.createElement("td");
tdtitle.textContent=week.title;

let tddescription=document.createElement("td");
tddescription.textContent = week.description || "No description";

let tdbutton=document.createElement("td");
let editbutton=document.createElement("button");
editbutton.textContent="Edit";
editbutton.classList.add("edit-btn");
editbutton.setAttribute("data-id", week.id);

let deletebutton=document.createElement("button");
deletebutton.textContent="Delete";
deletebutton.classList.add("delete-btn");
deletebutton.setAttribute("data-id" , week.id);

tdbutton.appendChild(editbutton);
tdbutton.appendChild(deletebutton);

tr.appendChild(tdtitle);
tr.appendChild(tddescription);
tr.appendChild(tdbutton);

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
  for(let i=0  ; i<weeks.length ; i++){
let weekrow = createWeekRow(weeks[i]);
tbody.appendChild(weekrow);
  }
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

    let t = document.getElementById('week-title').value.trim();
    let sd = document.getElementById('week-start-date').value;
    let d = document.getElementById('week-description').value.trim();

    let weekslink = document.getElementById('week-links').value;
    let links1 = weekslink.split('\n');

    let newweek = {
      id : `week_${Date.now()}` ,
      title : t ,
      startDate : sd ,
      description : d ,
      links : links1
     };

weeks.push(newweek);
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
  if(event.target.classList.contains('delete-btn')){
    let weekId = event.target.getAttribute('data-id');
    weeks = weeks.filter(week => week.id!==weekId);
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
  let response = await fetch('weeks.json');
  weeks= await response.json();

  renderTable();

  form.addEventListener('submit', handleAddWeek);
  tbody.addEventListener('click' , handleTableClick);
}

// --- Initial Page Load ---
// Call the main async function to start the application.
loadAndInitialize();
