function showNext(event, current) {
  event.preventDefault();
  document.getElementById(`step-${current}`).style.display = "none";
  document.getElementById(`step-${current + 1}`).style.display = "block";
  if (current === 5) {
    document.getElementById("next-5").disabled = true;
  }
  let para = document.getElementById("para");
  para.textContent = `Option ${current + 1} Has Loaded !`
  para.classList = "alert alert-success ps-5"
  window.scrollTo({
    top: 0, behavio: "smooth"
  });
}

function showPrevious(event, current) {
  event.preventDefault();
  document.getElementById(`step-${current}`).style.display = "none";
  document.getElementById(`step-${current - 1}`).style.display = "block";

  let para = document.getElementById("para");
  para.textContent = `Option ${current - 1} Has Loaded !`
  para.classList = "alert alert-success ps-5"
  window.scrollTo({
    top: 0, behavio: "smooth"
  });
}
let input = document.getElementById("questionImage");
let preview = document.getElementById("image-preview");
if (preview) {
  preview.style.display = "none";
}

input.addEventListener('change', () => {
  const file = input.files[0];
  preview.style.display = "block";
  if (file) {
    preview.src = URL.createObjectURL(file);
  }
});


let options_number = document.getElementById("options_number");
options_number.addEventListener("change", (event) => {
  const count = parseInt(event.target.value);
  for (let i = 1; i <= 5; i++) {
    toggleStep(i, i <= count)
  }
})

function toggleStep(step, show) {
  const div = document.getElementById(`step-${step}`)
  div.querySelectorAll("input, textarea, select").forEach(element => {
    element.disabled = !show;
  })
}
function here() {
  alert("here");
}
function before(current) {
  const div = document.getElementById(`question-${current}`);
  div.style.display = "none";
  const div1 = document.getElementById(`question-${current - 1}`);
  div1.style.display = "block";
}
function show(current) {
  const div = document.getElementById(`question-${current}`);
  div.style.display = "none";
  const div1 = document.getElementById(`question-${current + 1}`);
  div1.style.display = "block";
}
