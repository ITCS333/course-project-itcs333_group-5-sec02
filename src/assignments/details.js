/*
  Requirement: Populate the assignment detail page and discussion forum.

  Instructions:
  1. Link this file to `details.html` using:
     <script src="details.js" defer></script>

  2. In `details.html`, add the following IDs:
     - To the <h1>: `id="assignment-title"`
     - To the "Due" <p>: `id="assignment-due-date"`
     - To the "Description" <p>: `id="assignment-description"`
     - To the "Attached Files" <ul>: `id="assignment-files-list"`
     - To the <div> for comments: `id="comment-list"`
     - To the "Add a Comment" <form>: `id="comment-form"`
     - To the <textarea>: `id="new-comment-text"`

  3. Implement the TODOs below.
*/

// --- Global Data Store ---
// These will hold the data related to *this* assignment.
let currentAssignmentId = null;
let currentComments = [];

// --- Element Selections ---
// TODO: Select all the elements you added IDs for in step 2.
let titleE1 = document.getElementById('assignment-title');
let dueE1 = document.getElementById('assignment-due-date');
let descE1 = document.getElementById('assignment-description');
let filesE1 = document.getElementById('assignment-files-list');
let commentsE1 = document.getElementById('comment-list');
let formE1 = document.getElementById('comment-form');
let inputE1 = document.getElementById('new-comment-text');


// --- Functions ---

/**
 * TODO: Implement the getAssignmentIdFromURL function.
 * It should:
 * 1. Get the query string from `window.location.search`.
 * 2. Use the `URLSearchParams` object to get the value of the 'id' parameter.
 * 3. Return the id.
 */
function getAssignmentIdFromURL() {
  // ... your implementation here ...
  let params = new URLSearchParams(window.location.search);
  return params.get('id');
}

/**
 * TODO: Implement the renderAssignmentDetails function.
 * It takes one assignment object.
 * It should:
 * 1. Set the `textContent` of `assignmentTitle` to the assignment's title.
 * 2. Set the `textContent` of `assignmentDueDate` to "Due: " + assignment's dueDate.
 * 3. Set the `textContent` of `assignmentDescription`.
 * 4. Clear `assignmentFilesList` and then create and append
 * `<li><a href="#">...</a></li>` for each file in the assignment's 'files' array.
 */
function renderAssignmentDetails(assignment) {
  // ... your implementation here ...
  titleE1.textContent = assignment.title;
  dueE1.textContent = `Due: ${assignment.dueDate}`;
  descE1.textContent = assignment.description;
  filesE1.innerHTML = "";
  assignment.files.forEach(f => {
    let li = document.createElement('li');
    let link = document.createElement('a');
    link.href = f;
    link.textContent = f;
    li.appendChild(link);
    filesE1.appendChild(li);
  });
}

/**
 * TODO: Implement the createCommentArticle function.
 * It takes one comment object {author, text}.
 * It should return an <article> element matching the structure in `details.html`.
 */
function createCommentArticle(comment) {
  // ... your implementation here ...
  let article = document.createElement('article');

  let textp = document.createElement('p');
  textp.textContent = comment.text;

  let footer = document.createElement('footer');
  footer.textContent = `Posted by: ${comment.author}`;

  article.appendChild(textp);
  article.appendChild(footer);

  return article;

}

/**
 * TODO: Implement the renderComments function.
 * It should:
 * 1. Clear the `commentList`.
 * 2. Loop through the global `currentComments` array.
 * 3. For each comment, call `createCommentArticle()`, and
 * append the resulting <article> to `commentList`.
 */
function renderComments() {
  // ... your implementation here ...
  commentsE1.innerHTML= "";
  currentComments.forEach(c=>{
    let article = createCommentArticle(c);
    commentsE1.appendChild(article);
  });
}

/**
 * TODO: Implement the handleAddComment function.
 * This is the event handler for the `commentForm` 'submit' event.
 * It should:
 * 1. Prevent the form's default submission.
 * 2. Get the text from `newCommentText.value`.
 * 3. If the text is empty, return.
 * 4. Create a new comment object: { author: 'Student', text: commentText }
 * (For this exercise, 'Student' is a fine hardcoded author).
 * 5. Add the new comment to the global `currentComments` array (in-memory only).
 * 6. Call `renderComments()` to refresh the list.
 * 7. Clear the `newCommentText` textarea.
 */
function handleAddComment(event) {
  // ... your implementation here ...
  event.preventDefault();
  let commentsText = inputE1.value.trim();
  if(!commentsText) return;
  let newComment = {
    author : 'Student',
    text : commentsText
  };
  currentComments.push(newComment);
  renderComments();
  inputE1.value='';
}

/**
 * TODO: Implement an `initializePage` function.
 * This function needs to be 'async'.
 * It should:
 * 1. Get the `currentAssignmentId` by calling `getAssignmentIdFromURL()`.
 * 2. If no ID is found, display an error and stop.
 * 3. `fetch` both 'assignments.json' and 'comments.json' (you can use `Promise.all`).
 * 4. Find the correct assignment from the assignments array using the `currentAssignmentId`.
 * 5. Get the correct comments array from the comments object using the `currentAssignmentId`.
 * Store this in the global `currentComments` variable.
 * 6. If the assignment is found:
 * - Call `renderAssignmentDetails()` with the assignment object.
 * - Call `renderComments()` to show the initial comments.
 * - Add the 'submit' event listener to `commentForm` (calls `handleAddComment`).
 * 7. If the assignment is not found, display an error.
 */
async function initializePage() {
  // ... your implementation here ...
  currentAssignmentId = getAssignmentIdFromURL();
  if(!currentAssignmentId){
    alert('No assignment ID');
    return;
  }
  let [assignments, commentsData] = await Promise.all([
    fetch('assignments.json').then(r=> r.json()),
    fetch('comments.json').then(r=> r.json())
  ]);
  
  let assignment = assignments.find(a=> a.id === currentAssignmentId);
  currentComments = commentsData[currentAssignmentId] || [];

  if(assignment){
    renderAssignmentDetails(assignment);
    renderComments();
    formE1.addEventListener('submit', handleAddComment);
  }
  else{
    alert('Assignment not found');
  }


}

// --- Initial Page Load ---
initializePage();
