const sections = document.querySelectorAll(".form-section");
const guestForm = document.getElementById("guestForm");

const backBtn = document.getElementById("backBtn");
const nxtBtn = document.getElementById("nxtBtn");
const submitBtn = document.getElementById("submitBtn");

const errorMsg = document.querySelector(".error");
const newBtn = document.getElementById("newGuest");
const dashboardBtn = document.getElementById("dashboardBtn");

const confirmation = document.querySelector(".confirmation");
const message = document.querySelector(".message");
const formBtns = document.getElementById("formBtns")

let currentPage = 0;

function showPage(page) {

    sections.forEach(function(section) {
        section.style.display = "none";
    });

    sections[page].style.display = "block";

    formBtns.style.display = "block";

    backBtn.style.display = (page === 0) ? "none" : "inline-block";

    if (page === sections.length - 1) {
        nxtBtn.style.display = "none";
        submitBtn.style.display = "inline-block";
    } else {
        nxtBtn.style.display = "inline-block";
        submitBtn.style.display = "none";
    }

    confirmation.style.display = "none";

    errorMsg.textContent = "";
}

function validatePage(){
    const fields = sections[currentPage].querySelectorAll("input, select");

    for (let field of fields){
        if(field.value.trim() === ""){
            errorMsg.textContent = "Please ensure all fields are completed.";
            return false; 
        }
    }
    errorMsg.textContent = "";
    return true;
}

nxtBtn.addEventListener("click", function() {
    if (!validatePage()) {
        return;
    }

    if (currentPage < sections.length - 1) {
        currentPage++;
        showPage(currentPage);
    }

});

backBtn.addEventListener("click", function() {

    if (currentPage > 0) {
        currentPage--;
        showPage(currentPage);
    }

});

submitBtn.addEventListener("click", function() {


    sections.forEach(function(section) {
        section.style.display = "none";
    });

    formBtns.style.display = "none";
    errorMsg.style.display = "none";

    //Temp confirmation message
    message.textContent = "Guest information has been submitted successfully!";

    confirmation.style.display = "block";

});

newBtn.addEventListener("click", function() {

    guestForm.reset();

    currentPage = 0;
    showPage(currentPage);

});

dashboardBtn.addEventListener("click", function() {
    window.location.href = "security_homepage.php";
});


showPage(currentPage);