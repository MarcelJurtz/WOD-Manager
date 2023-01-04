var password = "Test123!";

$("#btn-generate").click(function() {
    var href = window.location.pathname;
    var dir = href.substring(0, href.lastIndexOf('/')) + "/";

    let path = new URL(window.location.origin + dir + 'index.php');
    path.searchParams.set('token', $('#token').val());
    path.searchParams.set('name', $('#name').val());

    window.location.href = path;
});

let previewUrl = function() {
    var href = window.location.pathname;
    var dir = href.substring(0, href.lastIndexOf('/')) + "/";

    var obj = {
        "token": $('#token').val(),
        "name": $('#name').val(),
        "date": new Date().toISOString()
    };

    var payload = JSON.stringify(obj);
    var encrypted = CryptoJS.AES.encrypt(payload, password);
    var param = atob(encrypted);

    let path = new URL(window.location.origin + dir + 'index.php');
    path.searchParams.set('token', param);
    // path.searchParams.set('name', $('#name').val());
    $('#api-url-info').text(path);

    $('#api-url-info-decrypted').text(CryptoJS.AES.decrypt(btoa(param), password));
}

$("#token").change(previewUrl);
$("#name").change(previewUrl);