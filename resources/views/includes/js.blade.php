@section('extra_scripts')
@parent

<script type="text/javascript">
$(document).ready(function() {

    var dataTable_state = false;  // I can make it dynamic if we want the system to remember the state.
    var table = $(".dataTable").DataTable({
        dom: "Blfrtp",
        lengthMenu: [5, 25, 50, 100, 250, 500, -1],
        buttons: ["copy", "excel", "pdf", "print",
            {
                text: 'Current Page',
                title: '',
                extend: 'excel',
                exportOptions: {
                    columns: ':visible:not(".not-export-col")',
                    modifier: {
                        page: 'current'
                    }
                }
            },                 
            "colvis"
        ],
        "columnDefs": [
            { "width": "4%", "targets": 0  },
		    { "width": "5%", "targets": -1 },
        ],
        'paging'      : true,
        'lengthChange': true,            
        'searching'   : true,            
        'ordering'    : true,            
        'stateSave'	  : dataTable_state,            
        'info'        : true,
        "autoWidth": false,
          
        "initComplete": function(settings, json) {
            $('div.datatable-loading').remove();
            $('.table').show();
        },
        
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/English.json"
        }, // I can make it dynamic if we want many languages.
        
        drawCallback: function() {
            if ($('#remove_buttons').length) {
                $('.buttons-copy').remove()
                $('.buttons-excel').remove()
                $('.buttons-pdf').remove()
                $('.buttons-print').remove()
            }            
        }
    });

    table.buttons().container().appendTo(".wrapper .col-md-6:eq(0)");

    CustomExport = {
        get_text: function (element)
        {
            return document.getElementById(element).innerText;
        },

        chosen_report: function (class_name)
        {
            return $('input[class=' + class_name + ']:checked').val();
        },

        fill_results_table: function (response, table)
        {
            $("#" + table + " tr").remove();
            results = jQuery.parseJSON(response[1]);
            // SQL text editor.
            $("#text-statement").val('').html(response[0]);
            html_ = '<tr>';

            $.each(results, function(key, value){
                $.each(value, function(k, v){
                    html_ += "<th>" + k + "</th>";
                });
                return false; //Break
            });


            html_ += '</tr>';

            $("#" + table).find('thead').append(html_);
            html_ = '';

            $.each(results, function(key, value){
                html_ += '<tr>';
                $.each(value, function(key, value){
                    html_ += '<td>' + value + '</td>';
                });
                html_ += '</tr>';
            });             

            $('#' + table).append(html_);
        },

        get_checkboxes_status: function (attr="input:checkbox")
        {
            var checkboxes={};
            checkboxes.tablesGranted=[];
            checkboxes.tablesDenied=[];

            $(attr).each(function(){
                var $this = $(this);

                if($this.is(":checked")) checkboxes.tablesGranted.push($this.attr("id"));
                else checkboxes.tablesDenied.push($this.attr("id"));            
            });

            return [checkboxes.tablesGranted, checkboxes.tablesDenied];
        }
    }
});

</script>
@endsection