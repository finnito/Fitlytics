document.addEventListener("DOMContentLoaded", function() {
    var urlParams = new URLSearchParams(window.location.search);
    var date = urlParams.get("date");
    console.log(date);
    
    if (date) {
        document.querySelectorAll("input[name='date']").forEach(function(el) {
            el.value = date;
        });
    }

    else {
        document.querySelectorAll("input[name='date']").forEach(function(el) {
            if (el.value.length == 0) {

                var dte = new Date();
                var year = dte.getFullYear();

                var month,
                    day;

                if (dte.getMonth() < 10) {
                    month = ("0" + (dte.getMonth() + 1));
                } else {
                    month = dte.getMonth() + 1;
                }

                if (dte.getDate() < 10) {
                    day = "0" + dte.getDate();
                } else {
                    day = dte.getDate();
                }
                var str = year + "-" + month + "-" + day;
                el.value = str;
            }
        });
    }
});
