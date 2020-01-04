@section('extra_scripts')
@parent

<script type="text/javascript">
$(document).ready(function() 
{

    var dataTable_state = ("{{ session('dataTable_state') }}" == 1)? true : false;

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
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/"+get_current_language()+".json"
        },
        
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
        get_text: function(element)
        {
            return document.getElementById(element).innerText;
        },

        chosen_report: function(class_name)
        {
            return $('input[class=' + class_name + ']:checked').val();
        },

        fill_results_table: function(response, table)
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

        get_checkboxes_status: function(attr="input:checkbox")
        {
            var checkboxes={};
            checkboxes.tablesGranted=[];
            checkboxes.tablesDenied=[];

            $(attr).each(function(){
                var $this = $(this);

                if($this.is(":checked")) {
                    checkboxes.tablesGranted.push($this.attr("id"));
                }
                else { 
                    checkboxes.tablesDenied.push($this.attr("id"));
                }       
            });

            return [checkboxes.tablesGranted, checkboxes.tablesDenied];
        },

        get_action_columns_status: function()
        {
            var checked_columns = CustomExport.get_checkboxes_status(".tables-columns:input:checkbox")[0];
            var checkboxes={};
            checkboxes.tablesGranted=[];

            for (var i = 0; i<checked_columns.length; i++) {
                //Not ready
            }

            return ['assess_results.ar_id-SELECT', 'assess_results.assessor_1-MAX', 'assessors.id-AVG'];
        },

        select_actions: function(select_element) {
            for (var i = 0; i < select_element.length; i++) {
                //Not ready
            }
        },

        click_checkboxes: function(checkboxes)
        {
            for (var i = 0; i < checkboxes.length; i++) {
                var element = document.getElementById(checkboxes[i]);

                if(element !== null) {
                    element.click()
                }
            }            
        },

        search: function(input_id, table_id)
        {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById(input_id);
            filter = input.value.toUpperCase();
            table = document.getElementById(table_id);
            tr = table.getElementsByTagName("tr");

            // Loop through all table rows, and hide those who don't match the search query
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];

                if(td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } 
                    else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    }

    {{-- Get Language. If you need more languages change the code to switch. --}}
    function get_current_language() 
    {   
        var locale = document.getElementById('current_locale').value;

        switch(locale) {
            case 'es':
                return 'Spanish';
            default:
                return 'English';
        } 
    }
});

</script>
@endsection