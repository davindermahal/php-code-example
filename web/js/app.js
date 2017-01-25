(function init() {
    $( document ).ready(function() {
        $("#remove-btn").on("click", function(event) {
            var checkboxes = $("#url-list [type='checkbox']:checked");
            var checkboxIds = [];
            var formData;

            for (var checkbox of checkboxes) {
                checkboxIds.push(checkbox['value']);
            }

            if (checkboxIds.length !== 0) {
                formData = {'items': checkboxIds.toString(), 'isXhr': true };

                $.ajax({
                    url: "/filter/remove",
                    type: "POST",
                    data: formData,
                    success: function(data)
                    {
                        data.items.forEach(function deleteRow(id) {
                            $("#row_" + id).remove();
                        });

                        if ($("#url-list table tbody").children().length === 0) {
                            $("#url-remove-form").remove();
                            $("#url-list").append("<h4>There are no URLs to filter.</h4>");
                        }

                        if (data.iframeView !== false) {
                            $("#blocked").replaceWith(data.iframeView);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        var markup = '<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span> </button> <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span>There has been an error. Please try again later. </div>';
                        $("#base-alerts").append(markup);
                    }
                });
            }

            event.preventDefault();
        });
    });
})();