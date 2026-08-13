let optionImagePreview = document.getElementById('optionPreview');
let optionImageInput = document.getElementById("optionInput");
optionImageInput.addEventListener("change" , () => {
  let file = optionImageInput.files[0];
  if(file){
    optionImagePreview.src = URL.createObjectURL(file);
  }
})