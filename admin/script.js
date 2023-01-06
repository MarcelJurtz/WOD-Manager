// TODO Check wods for max length / reduce font size?
// var workoutlength = <?php echo strlen($wod) ?>;
// if (workoutlength > 200) {
//     location.reload();
//     alert("Workout is too big for the image!");
// }

let details = document.getElementById("details");
let btnCopy = document.getElementById("copy");

let btnCopyTextInit = document.getElementById("btn-copy-init");
let btnCopyTextCopied = document.getElementById("btn-copy-copied");

let btnChangeBg = document.getElementById("replace-bg");
let btnGetRandom = document.getElementById("get-random");

let preview = document.getElementById("preview");

details.textContent = details.textContent.trim();

btnCopy.addEventListener('click', function(event) {
    navigator.clipboard.writeText(details.textContent).then(function() {
        btnCopyTextInit.classList.add("d-none");
        btnCopyTextCopied.classList.remove("d-none");
        setTimeout(function() {
            btnCopyTextInit.classList.remove("d-none");
            btnCopyTextCopied.classList.add("d-none");
        }, 1500);
    }, function(err) {
        console.error('Async: Could not copy text: ', err);
    });
});

btnChangeBg.addEventListener('click', function() {

    // Add query param if it isn't set already
    // let permalink = document.getElementById("details").dataset.permalink;
    // window.location = window.location.href.split('?')[0] + "?wod=" + permalink;
    let src = preview.src;
    preview.src = null;
    preview.src = src;
});

btnGetRandom.addEventListener('click', function() {
    window.location = window.location.href.split('?')[0];
});