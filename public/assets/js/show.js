
let body = document.body;
function popout(id) {
  let div = document.getElementById(`popout-${id}`);
  div.style.display = "block";
  let overlay = document.querySelector(".overlay");
  overlay.style.display = "block"
  overlay.style.transition = "filter 0.3s ease";
}

let overlay = document.querySelector(".overlay");


function closeButton(id) {
  let div = document.getElementById(`popout-${id}`);
  div.style.display = "none";
  let overlay = document.querySelector(".overlay");
  overlay.style.display = "none"
  overlay.style.transition = "filter 0.3s ease";
}
function closeOverlay(current) {
  overlay.style.display = "none"
  let popout = document.getElementById(`popout-${current}`);s
  popout.style.display = "none";
}