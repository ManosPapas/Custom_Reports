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

            return checkboxes.tablesGranted;
        },

        select_actions: function(select_element) {
            for (var i = 0; i < select_element.length; i++) {
                //Not ready
            }
        },

        click_checkboxes: function(checkboxes)
        {
            if(checkboxes.length > 0){
                for (var i = 0; i < checkboxes.length; i++) {
                    var element = document.getElementById(checkboxes[i]);

                    if(element !== null) {
                        element.click();
                    }
                }
            }
        },

        search: function(input_id, table_id)
        {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById(input_id);
            filter = input.value.toUpperCase();
            table = document.getElementById(table_id);
            tr = table.getElementsByTagName("tbody")[0].rows;;

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
        },

        check_all: function(id, class_name) 
        {
            $("#"+id).change(function()
            {
                $("."+class_name).prop('checked', $(this).prop("checked")); 
            });

            // Do not do that for tables, it will take too long! The user should unclick one checkbox manually and it will work as intented (to avoid overhead in the system).
            if(class_name === 'tables-columns') {
                checkboxes = $('.'+class_name).map(function()
                {
                    return $(this).attr('id');
                }).get();

                CustomExport.click_checkboxes([checkboxes[0]]);
            }            
        },

        fill_where_columns: function()
        {
            $.ajax({
                url: '/custom-reports/get-table-columns',
                method: 'GET',
                data: {
                    tables: checked_tables
                },
                success: function(columns)
                {
                    html = '';

                    for(var i = 0; i<columns.length; i++) {
                        html += "<option class='where-columns' value="+ columns[i] + ">" + columns[i] + "</option>";
                    }

                    $("#where-columns option").remove();
                    $('#where-columns').append(html);

                },
                error: function()
                { 
                   console.log('Something went wrong with the second ajax!');
                }
            });
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