<?php ?>
<!doctype html>
<html>
    <head>
        <title>Admin ui</title>
        <link rel="stylesheet" href="css/bootstrap.min.css" />
        <link rel="stylesheet" href="css/main.css">
        <link href="favicon.ico" rel="shortcut icon">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>

        <main>
            <h1 class="pageTitle">Admin UI</h1>
            <!--<label>Search Name:</label>-->

            <div class="row">
                <div class="col-sm-9">
                    <div class="form-inline">
                        <input type="text" name="serchbar" class="form-control" id="serchbar" placeholder="Search by name,email or role" /> 
                    </div>
                </div>
                <div class="col-sm-3">
                    <button class="btn btn-primary" id="search">Search</button>
                    <button class="btn btn-info" id="reset">Reset</button>
                </div>
            </div>


            <table id="members">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectCheckbox" data-id="0" /></th>
                        <th>id</th>
                        <th>username</th>
                        <th>email</th>
                        <th>role</th> 


                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div id="table-footer">
                <div class="row">
                    <div class="col-sm-3">

                        <button class="deleteSelected">Delete Selected</button>
                    </div>

                    <div class="col-sm-9">
                        <div id="pagination">

                        </div> 
                    </div>
                </div>

            </div>

        </main>



        <script src="js/jquery.min.js"></script>
        <script src="js/jquery.tabledit.js"></script>

        <script type="text/javascript"">

            var currentPage = 1;
            var itemsPerPage = 10;
            var noOfRows = 0;
            var noOfPages = 0;
            var memberData = [];
            var deleterows = [];
            var checked = false;

            $(document).ready(function () {

                init();

                $("#serchbar").on("keyup", function () {
                    var searchKey = $("#serchbar").val();

                    console.log(searchKey);

                    if (searchKey.length >= 2) {
                        var domain = "http://localhost/admin_ui/data.php?";
                        $.get(domain + "action=search&search_key=" + searchKey, function (data) {
                            memberData = data.Records;
                            console.log(memberData);
                            noOfRows = memberData.length;

                            noOfPages = noOfRows / itemsPerPage;
                            drawPaginatedTable();
                        });
                    }
                });


                $("#reset").on("click", function () {
                    $("#serchbar").val("");
                    init();
                });


                $(document).on("click", "#pagination .page", function () {
                    var page = $(this).data("page");

                    if (page !== 'prev' && page !== 'next') {
                        currentPage = page;

                    } else if (page.trim().toLowerCase() === 'prev') {
                        if (parseInt(currentPage) > 1) {
                            currentPage = currentPage - 1;
                        }

                    } else if (page === 'next' && noOfRows !== currentPage) {
                        if (parseInt(currentPage) <= noOfPages) {
                            currentPage = currentPage + 1;
                        }
                    }

                    createTable();

                });


                $(document).on("click", ".table_row .selectCheckbox", function () {
                    var rowId = $(this).data("id");
                    $('table_row').toggleClass("selectCheckbox");
                    deleterows.push(rowId);

                });


                $("#selectCheckbox").click(function () {
                    var rowId = $(this).data("id");
                    var start = (itemsPerPage * currentPage) - 9;
                    var end = itemsPerPage * currentPage;
                    for (i = start; i <= end; i++) {
                        deleterows.push(i);

                    }

                    $(".selectCheckbox").prop('checked', $(this).prop('checked'));
                    $('tbody tr td').toggleClass("selectCheckbox");
                });



                $('.deleteSelected').click(function () {
                    $.post("data.php", {action: 'deleteAll', deleteIds: deleterows.join("|")}, function (data) {
                        memberData = data.Records;

                        noOfRows = memberData.length;
                        noOfPages = noOfRows / itemsPerPage;
                        drawPaginatedTable();

                    });
                });
            });


            function init() {
                $.get("data.php", function (data) {
                    memberData = data.Records;
                    noOfRows = memberData.length;
                    noOfPages = noOfRows / itemsPerPage;
                    drawPaginatedTable(data.Records);
                });
            }


            function drawPaginatedTable() {
                drawPages(noOfPages);
                createTable(memberData, currentPage, itemsPerPage);
            }


            function createTable() {

                var rows = "";
                var start = (currentPage * itemsPerPage) - itemsPerPage;
                var end = start + itemsPerPage - 1;

                $.each(memberData, function (i, obj) {
                    if (i >= start && i <= end) {
                        rows += "<tr class=\"table_row\" >\n\
                            <td><input type=\"checkbox\" class='selectCheckbox'  data-id='" + obj.id + "' /></td>\n\
                            <td>" + obj.id + "</td>\n\
                            <td>" + obj.name + "</td>\n\
                            <td>" + obj.email + "</td>\n\
                            <td>" + obj.role + "</td>\n\
                        </tr>";
                    }
                });

                $('#members tbody').html(rows);
                applyTableEditor();
            }


            function applyTableEditor() {
                $('#members').Tabledit({
                    url: 'data.php',
                    columns: {
                        identifier: [1, 'id'],
                        editable: [[2, 'name'], [3, 'email'], [4, 'role']]
                    },
                    onDraw: function () {

                    },
                    onSuccess: function (data, textStatus, jqXHR) {
                        console.log('onSuccess(data, textStatus, jqXHR)');
                        console.log(data);
                        console.log(textStatus);
                        console.log(jqXHR);

                    },
                    onFail: function (jqXHR, textStatus, errorThrown) {
                        console.log('onFail(jqXHR, textStatus, errorThrown)');
                        console.log(jqXHR);
                        console.log(textStatus);
                        console.log(errorThrown);
                    },
                    onAlways: function () {
                        console.log('onAlways()');
                    },
                    onAjax: function (action, serialize) {
                        console.log('onAjax(action, serialize)');
                        console.log(action);
                        console.log(serialize);
                    }
                });
            }


            function drawPages() {
                var pageHtml = "";
                console.log("In pages", noOfPages);

                for (var i = 1; i <= noOfPages + 1; i++) {
                    pageHtml += "<li class='page' data-page='" + i + "'>" + i + "</li>";
                }

                var html = "<ul class='pageList list-inline'><li class='page' data-page='prev'>Prev</li>" + pageHtml + "<li class='page' data-page='next'>Next</li></ul>";

                $("#pagination").html(html);
            }

        </script>
    </body>

</html>
