document.addEventListener("DOMContentLoaded", function () {
    var optionsButton = document.getElementById("options-button");
    var optionsContent = document.getElementById("options");

    optionsButton.addEventListener("click", function () {
        if (optionsContent.style.display === "none" || optionsContent.style.display === "") {
            optionsContent.style.display = "block";
        } else {
            optionsContent.style.display = "none";
        }
    });
});
