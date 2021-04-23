$(document).ready(function () {

    var toReload = $("span[name='to-reload-page']");
    if (toReload.length) {
        var reloadInterval = toReload.data('interval');
        setTimeout(function () {
            document.location.reload()
        }, reloadInterval * 1000 * 60);
    }

    $("input#checkAll").click(function () {
        var lines = $("input.line"), checkedAll = $(this).prop('checked');
        lines.prop('checked', checkedAll);
    });


});
