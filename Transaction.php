<?php
require 'include/db.php';
include 'include/header.php';
$groups = $pdo->query("SELECT * FROM `groups` ORDER BY name")->fetchAll();
?>
<!-- ////////////////////////////////////////////////////// -->
<style>
  /* [Insert the CSS code from Section 1 here] */
  /* --- START OF CSS --- */
  :root {
    /* Theme Colors (Light Theme) */
    --ds-light-bg: #f8f9fa;
    --ds-card-bg: #ffffff;
    --ds-primary-highlight: #4f46e5;
    /* Indigo */
    --ds-success-color: #10b981;
    /* Emerald Green */
    --ds-deposit-color: #f59e0b;
    /* Amber */
    --ds-text-color: #1f2937;
    --ds-sub-text-color: #6b7280;
    --ds-border-color: #e5e7eb;
    --ds-shadow-premium: 0 5px 20px rgba(0, 0, 0, 0.08);
  }

  body {
    background-color: var(--ds-light-bg);
    font-family: 'Inter', sans-serif;
    color: var(--ds-text-color);
  }

  .ds-tracker__container {
    max-width: 1400px;
  }

  .ds-tracker__card {
    background-color: var(--ds-card-bg);
    border-radius: 12px;
    box-shadow: var(--ds-shadow-premium);
    /* padding: 20px; */
    margin-bottom: 30px;
    border: 1px solid var(--ds-border-color);
    transition: transform 0.3s ease;
  }

  .ds-tracker__card:hover {
    /* transform: translateY(-3px); */
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
  }

  .ds-tracker__metric {
    padding: 20px;
    border-radius: 8px;
    background-color: #f3f4f6;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    border-left: 5px solid var(--ds-primary-highlight);
  }

  .ds-tracker__metric-value {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--ds-text-color);
  }

  .ds-filter__select,
  .ds-filter__input {
    border-radius: 8px;
    border: 1px solid var(--ds-border-color);
    background-color: var(--ds-card-bg);
    color: var(--ds-text-color);
    padding: 12px 15px;
    transition: border-color 0.3s;
  }

  .ds-filter__input::placeholder {
    color: var(--ds-sub-text-color);
    opacity: 0.8;
  }

  .ds-filter__select:focus,
  .ds-filter__input:focus {
    border-color: var(--ds-primary-highlight);
    box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
    background-color: var(--ds-card-bg);
    color: var(--ds-text-color);
  }

  .ds-table {
    width: 100%;
  }

  .ds-table thead th {
    background-color: var(--ds-primary-highlight);
    color: white;
    padding: 18px 15px;
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .ds-table tbody tr {
    transition: background-color 0.2s ease;
  }

  .ds-table tbody tr:hover {
    background-color: #f3f4f6;
  }

  .ds-table td {
    padding: 15px;
    border-bottom: 1px solid var(--ds-border-color);
    color: var(--ds-text-color);
  }

  .ds-amount--total {
    font-weight: 700;
    color: var(--ds-success-color);
    font-size: 1.2em;
  }

  .ds-amount--deposit {
    font-weight: 600;
    color: var(--ds-deposit-color);
  }

  .ds-amount--muted {
    color: var(--ds-sub-text-color);
  }

  .ds-text-primary {
    color: var(--ds-primary-highlight) !important;
  }

  .ds-text-success {
    color: var(--ds-success-color) !important;
  }

  .ds-text-muted {
    color: var(--ds-sub-text-color) !important;
  }

  .nav-pills .nav-link:not(.active) {
    color: #212529;
    background-color: transparent;
  }

  .nav-pills .nav-link.active {
    background-color: #007bff !important;
    color: white !important;
    font-weight: 600;
  }
  
  
  
  /* Button ka main style */
  .dt-buttons .buttons-html5 {
    background: #2563eb !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 8px 16px !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    cursor: pointer !important;
  }

  .dt-buttons .buttons-html5:hover {
    background: #1d4ed8 !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
  }

  .dt-buttons .buttons-html5:active {
    transform: translateY(0px) !important;
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06) !important;
  }

  #example_filter input {
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    padding: 6px 12px !important;
    outline: none !important;
    transition: border-color 0.2s !important;
  }

  #example_filter input:focus {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
  }

  #example_length select {
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    padding: 4px 8px !important;
    outline: none !important;
  }

  #example_paginate .paginate_button {
    background: #f3f4f6 !important;
    color: #374151 !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    padding: 6px 12px !important;
    margin-left: 4px !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    text-decoration: none !important;
  }

  #example_paginate .paginate_button.current {
    background: #2563eb !important;
    color: white !important;
    border-color: #2563eb !important;
    font-weight: bold !important;
  }

  #example_paginate .paginate_button.current:hover {
    color: white !important;
  }

  #example_paginate .paginate_button:hover:not(.current):not(.disabled) {
    background: #e5e7eb !important;
    color: #2563eb !important;
    border-color: #2563eb !important;
  }

  #example_paginate .paginate_button.disabled {
    background: #f9fafb !important;
    color: #9ca3af !important;
    cursor: not-allowed !important;
    border-color: #e5e7eb !important;
  }
  
  
  .highlight-red td {
    background-color: #ffe5e5 !important;
    /* Light Red */
    color: #b71c1c !important;
    /* Dark Red text */
    font-weight: bold;
  }

  /* Hover karne par bhi color rehna chahiye */
  table.dataTable tbody tr.highlight-red:hover td {
    background-color: #ffcccc !important;
  }
  

  /* --- END OF CSS --- */
</style>
<div class="container ds-tracker__container my-5">
  <input type="hidden" name="member_id" value="<?php echo $id ?>" id="member_id">

  <div class="card ds-tracker__card  border-0 bg-white rounded-4">

    <div class="card-body p-3">

      <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">
        <i class="bi bi-funnel-fill me-2 text-primary"></i> Filter & Search Transactions
      </h5>

      <div class="row g-3 align-items-end">

        <div class="col-md-3 col-sm-6">
          <div class="form-group">
            <label for="payment_month_filter" class="form-label small fw-medium text-muted">Filter by Month</label>
            <select name="payment_month_filter" id="payment_month_filter" class="form-select form-select-sm shadow-sm">
              <!-- <option value="">All</option> -->
              <?php for ($m = 1; $m <= 12; $m++):
                $sel = (isset($_POST['month']) && intval($_POST['month']) == $m) || (!isset($_POST['month']) && $m == intval(date('n'))) ? 'selected' : '';
              ?>
                <option value="<?= $m ?>" <?= $sel ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>

        <div class="col-md-3 col-sm-6">
          <div class="form-group">
            <label for="payment_year_filter" class="form-label small fw-medium text-muted">Filter by Year</label>
             <?php $default_year = 2025; ?>
            <select name="payment_year_filter" id="payment_year_filter" class="form-select form-select-sm shadow-sm">
              <?php for ($y = 2024; $y <= 2035; $y++): ?>
              <option value="<?php echo $y; ?>" <?php echo ($y == $default_year) ? 'selected' : ''; ?>>
                  <?php echo $y; ?>
                </option>
               <?php endfor; ?>
            </select>
          </div>
        </div>

        <div class="col-md-3 col-sm-6">
          <div class="form-group">
            <label for="" class="form-label small fw-medium text-muted">Group</label>
            <select id="filter_group" class="form-select form-select-sm shadow-sm">
              <option value="">All Groups</option>
              <?php foreach ($groups as $g): ?>
                <option value="<?= htmlspecialchars($g['id']) ?>">
                  <?= htmlspecialchars($g['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        
         <!-- payment method  -->
         <div class="col-md-3 col-sm-6">
          <div class="form-group">
            <label for="" class="form-label small fw-medium text-muted">payment Method</label>
            <select id="filter_payment_method" class="form-select form-select-sm shadow-sm">
              <option value="">All Method</option>
              <option value="cash">Cash</option>
              <option value="online">Online</option>
              <option value="cheque">Cheque</option>
            </select>
          </div>
        </div>

        <!-- <div class="col-md-2 col-sm-12 d-flex justify-content-start">
          <button class="btn btn-danger btn-sm w-100" type="button">
            <i class="bi bi-x-circle me-1"></i> Reset
          </button>
        </div> -->

      </div>


      <div class="row mt-2 g-3 align-items-center">
        <div class="col-md-12">
          <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">
            <i class="bi bi-funnel-fill me-2 text-primary"></i> Payment History
          </h5>
          <div class="table-responsive rounded">
            <table class="table ds-table mb-0 table-hover" id="example">
              <thead class="table-light">
                <tr>
                  <th>
                    <input type="checkbox" id="select_all">
                  </th>
                  <th>Name</th>
                  <th>Open M.S</th>
                  <th>Open Loan</th>
                  <th>MS</th>
                  <th>RCV</th>
                  <th>INT</th>
                  <th>PF</th>
                  <th>Fine</th>
                  <th>Total</th>
                  <th>L.N</th>
                  <th>BAL LN</th>
                  <th>GTMS</th>
                  <th>Pay Method</th>
                  <th>Expected Amount</th>
                </tr>
              </thead>
              <tbody id="member_detail_table_body">

              </tbody>
              <tfoot>
                <tr>
                  <th></th> <!-- select skip -->
                  <th>Total:</th>
                  <th></th> <!-- open_ms -->
                  <th></th> <!-- open_loan -->
                  <th></th> <!-- ms -->
                  <th></th> <!-- rcv -->
                  <th></th> <!-- int -->
                  <th></th> <!-- pf -->
                  <th></th> <!-- fine -->
                  <th></th> <!-- total -->
                  <th></th> <!-- ln -->
                  <th></th> <!-- bal_ln -->
                  <th></th> <!-- gtms -->
                  <th></th> <!-- pay_method -->
                  <th></th> <!-- expected_amount -->
                </tr>
              </tfoot>

            </table>
            <input type="hidden" id="mtd">
            <input type="hidden" id="std">


            <p id="no-results-message" class="text-center ds-text-muted py-4 d-none">
              No results found matching your criteria. Try adjusting your filters.
            </p>

            <div id="gtms-group-breakdown" class="mt-3 p-3 border rounded d-none" style="background:#f8fafc;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>GTMS Group Breakdown (All Groups check)</strong>
                <span id="gtms-diff-label" class="small"></span>
              </div>
              <div class="table-responsive">
                <table class="table table-sm mb-0" id="gtms-group-table">
                  <thead>
                    <tr>
                      <th>Group</th>
                      <th class="text-end">Members</th>
                      <th class="text-end">GTMS</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                  <tfoot>
                    <tr>
                      <th>Total</th>
                      <th class="text-end" id="gtms-bd-members">0</th>
                      <th class="text-end" id="gtms-bd-total">0</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>


    </div>
  </div>


</div>


<!-- //////////////////////////////////////////////// -->

<script>
  $(document).ready(function() {
    loadMemberDetails();
    $("#payment_year_filter, #payment_month_filter, #filter_group, #filter_payment_method")
      .off('change.loadMembers')
      .on('change.loadMembers', loadMemberDetails);
  });

  function loadMemberDetails() {
    let month = $("#payment_month_filter").val();
    let year = $("#payment_year_filter").val();
    let group = $("#filter_group").val();
    let payment_method = $("#filter_payment_method").val();

    
    var group_name = $('#filter_group option:selected').text() === '' ? 'All' : $('#filter_group option:selected').text();

    if (!year) {
      alert("Please select year");
      return;
    }
    
    const monthArray = {
      1: 'Jan',
      2: 'Feb',
      3: 'Mar',
      4: 'Apr',
      5: 'May',
      6: 'Jun',
      7: 'Jul',
      8: 'Aug',
      9: 'Sep',
      10: 'Oct',
      11: 'Nov',
      12: 'Dec'
    };

    if ($.fn.DataTable.isDataTable('#example')) {
      $('#example').DataTable().destroy();
    }

    table = $('#example').DataTable({
        pageLength: 35,
      ajax: {
        url: 'action/ajax_all_member_details.php',
        type: 'POST',
        data: {
          month: month,
          year: year,
          group: group,
          payment_method:payment_method

        },
        
         dataSrc: function(json) {
          // Server-side totals (esp. GTMS) — All Groups vs group-wise match ke liye
          window.reportTotals = json.totals || null;
          window.reportByGroup = json.by_group || [];
          renderGtmsGroupBreakdown(json);
          if (json.data && json.data.length > 0) {
            currentMtd = json.data[0].mtd; 
            currentStd = json.data[0].std; 
          
            $("#mtd").val(currentMtd);
            $("#std").val(currentStd);
          }
          return json.data || [];
        }
        
        
      },
      columns: [{
          data: 'select',
          orderable: false,
          searchable: false
        },
        {
          data: 'name'
        },
        {
          data: 'open_ms',
          className: 'text-right',
          render: $.fn.dataTable.render.number(',', '.', 2)
        },
        {
          data: 'open_loan',
          className: 'text-right',
          render: $.fn.dataTable.render.number(',', '.', 2)
        },
        {
          data: 'ms',
          className: 'text-right',
          render: $.fn.dataTable.render.number(',', '.', 2)
        },
        {
          data: 'rcv',
          className: 'text-right',
          render: $.fn.dataTable.render.number(',', '.', 2)
        },
        {
          data: 'int',
          className: 'text-right',
          render: $.fn.dataTable.render.number(',', '.', 2)
        },
        {
          data: 'pf',
          className: 'text-right',
          render: $.fn.dataTable.render.number(',', '.', 2)
        },
        
        {
          data: 'fine',
          className: 'text-right',
          render: $.fn.dataTable.render.number(',', '.', 2)
        },
        {
          data: 'total',
          className: 'text-right',
          render: $.fn.dataTable.render.number(',', '.', 2)
        },
        {
          data: 'ln',
          className: 'text-right',
          render: $.fn.dataTable.render.number(',', '.', 2)
        },
        {
          data: 'bal_ln',
          className: 'text-right',
          render: $.fn.dataTable.render.number(',', '.', 2)
        },
        {
          data: 'gtms',
          className: 'text-right',
          render: $.fn.dataTable.render.number(',', '.', 2)
        },
         {
          data: 'payment_method',
        },
         {
          data: 'expected_amount',
          className: 'text-right',
          render: $.fn.dataTable.render.number(',', '.', 2)
        }
      ],
      dom: 'Bfrtip',
      buttons: [{
        extend: 'excelHtml5',
        text: 'Export Selected',
        //title: `Member Report - ${month}-${year}-${group_name.trim()}`,
        
        title: function() {
          var mtd = $('#mtd').val();
          var std = $('#std').val();
          let group_name = $("#filter_group option:selected").text().trim();


          // Title return karega mtd aur std ke saath
          return `Member Report (${std} - ${mtd}) | ${monthArray[month]}-${year} - (${group_name})`;
        },
        
        
        exportOptions: {
          rows: function(idx, data, node) {
            return $(node).find('.row-select').prop('checked');
          },
          columns: ':not(:first-child)',
        },
        customizeData: function(data) {
          var api = table;
          var footerRow = [];

          footerRow.push('Total:');

          var columnsToTotalKeys = [
            'open_ms',
            'open_loan',
            'ms',
            'rcv',
            'int',
            'pf',
            'fine',
            'total',
            'ln',
            'bal_ln',
            'gtms'
          ];

          var intVal = function(i) {
            var cleaned = typeof i === 'string' ? i.replace(/,/g, '') : i;
            return parseFloat(cleaned) || 0;
          };

          columnsToTotalKeys.forEach(function(colKey) {

            var total = api
              .rows(function(idx, data, node) {
                return $(node).find('.row-select').prop('checked');
              })
              .data()
              .pluck(colKey)
              .reduce(function(a, b) {
                return intVal(a) + intVal(b);
              }, 0);

            footerRow.push(total.toLocaleString(undefined, {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            }));
          });

          data.body.push(footerRow);
        },
        customize: function(xlsx) {
          var sheet = xlsx.xl.worksheets['sheet1.xml'];
          var styles = xlsx.xl['styles.xml'];

          // 1. Pink/Red Style Setup
          var fills = $('fills', styles);
          fills.append('<fill><patternFill patternType="solid"><fgColor rgb="FFFFC7CE"/><bgColor indexed="64"/></patternFill></fill>');
          var fillIdx = fills.children().length - 1;
          var cellXfs = $('cellXfs', styles);
          cellXfs.append('<xf numFmtId="0" fontId="0" fillId="' + fillIdx + '" borderId="0" applyFill="1"/>');
          var redStyleIdx = cellXfs.children().length - 1;

          var api = $('#example').DataTable();

          // 2. Sirf Selected Data nikalna (Export logic se match karne ke liye)
          var exportedData = [];
          api.rows().every(function(rowIdx, tableLoop, rowLoop) {
            var node = this.node();
            if ($(node).find('.row-select').prop('checked')) {
              exportedData.push(this.data());
            }
          });

          // 3. Excel rows par loop (Header aur Title skip karne ke liye)
          var excelRows = $('row', sheet);
          var dataRowCounter = 0;

          excelRows.each(function(i) {
            // i = 0 (Title Row), i = 1 (Header Row) - Dono ko skip karega
            if (i > 1) {
              var row = $(this);
              var rowData = exportedData[dataRowCounter];

              // Agar rowData mil raha hai aur is_unpaid true hai
              if (rowData) {
                if (rowData.is_unpaid === true) {
                  // Poori row ke cells ko pink style dena
                  row.children('c').attr('s', redStyleIdx);
                }
                // Counter tabhi badhega jab hum actual data row par honge
                dataRowCounter++;
              }
            }
          });

          // 4. Footer (Total) row ko bold style dena
          $('row', sheet).last().children('c').attr('s', '2');
        }
      }],
      order: [
        [1, 'asc']
      ],
      footerCallback: function(row, data, start, end, display) {
        var api = this.api();
        var intVal = function(i) {
          var cleaned = typeof i === 'string' ? i.replace(/,/g, '') : i;
          return parseFloat(cleaned) || 0;
        };

        var columnsToTotal = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        // Prefer server totals for Open MS / MS / GTMS (All Groups accuracy)
        var serverMap = {
          2: window.reportTotals ? window.reportTotals.open_ms : null,
          4: window.reportTotals ? window.reportTotals.ms : null,
          12: window.reportTotals ? window.reportTotals.gtms : null
        };

        columnsToTotal.forEach(function(colIndex) {
          var total;
          if (serverMap[colIndex] !== null && serverMap[colIndex] !== undefined) {
            total = intVal(serverMap[colIndex]);
          } else {
            total = api
              .column(colIndex, { page: 'all' })
              .data()
              .reduce(function(a, b) {
                return intVal(a) + intVal(b);
              }, 0);
          }

          $(api.column(colIndex).footer()).html(
            total.toLocaleString(undefined, {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            })
          );
        });
      }

    });
  }

  function renderGtmsGroupBreakdown(json) {
    var box = $('#gtms-group-breakdown');
    var groupFilter = $('#filter_group').val();
    var rows = json.by_group || [];
    var totals = json.totals || {};

    // Only show when All Groups selected
    if (groupFilter) {
      box.addClass('d-none');
      return;
    }

    var tbody = box.find('tbody');
    tbody.empty();
    var sumGtms = 0;
    var sumMembers = 0;

    rows.forEach(function(g) {
      sumGtms += parseFloat(g.gtms) || 0;
      sumMembers += parseInt(g.members, 10) || 0;
      tbody.append(
        '<tr>' +
          '<td>' + $('<div>').text(g.group_name).html() + ' <small class="text-muted">#' + g.group_id + '</small></td>' +
          '<td class="text-end">' + g.members + '</td>' +
          '<td class="text-end">' + Number(g.gtms).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
        '</tr>'
      );
    });

    $('#gtms-bd-members').text(sumMembers);
    $('#gtms-bd-total').text(sumGtms.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

    var allGtms = parseFloat(totals.gtms) || 0;
    var diffLabel = 'Groups in result: ' + rows.length +
      ' | All GTMS: ' + allGtms.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (rows.some(function(g) { return !g.group_id; })) {
      diffLabel += ' | ⚠ No Group members included';
    }
    $('#gtms-diff-label').text(diffLabel);
    box.removeClass('d-none');
  }

  // Select all checkbox
  $(document).on('change', '#select_all', function() {
    $('.row-select').prop('checked', $(this).prop('checked'));
  });
</script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- Buttons JS -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<!-- Excel Export (JSZip) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<!-- PDF Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<?php include 'include/footer.php'; ?>
