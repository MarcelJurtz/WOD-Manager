// TODO Check wods for max length / reduce font size?
// var workoutlength = <?php echo strlen($wod) ?>;
// if (workoutlength > 200) {
//     location.reload();
//     alert("Workout is too big for the image!");
// }

let details = document.getElementById("details");
let btnCopy = document.getElementById("copy");

let btnChangeBg = document.getElementById("replace-bg");
let btnGetRandom = document.getElementById("get-random");

details.textContent = details.textContent.trim();

btnCopy.addEventListener('click', function(event) {
    navigator.clipboard.writeText(details.textContent).then(function() {
        btnCopy.innerHTML = "Copied! <i class='fa fa-check-circle'></i>";
        setTimeout(function() {
            btnCopy.innerHTML = 'Copy <i class="fa-regular fa-copy"></i>';
        }, 1500);
    }, function(err) {
        console.error('Async: Could not copy text: ', err);
    });
});

btnChangeBg.addEventListener('click', function() {

    // Add query param if it isn't set already
    let permalink = document.getElementById("details").dataset.permalink;
    window.location = window.location.href.split('?')[0] + "?wod=" + permalink;
});

btnGetRandom.addEventListener('click', function() {
    window.location = window.location.href.split('?')[0];
});