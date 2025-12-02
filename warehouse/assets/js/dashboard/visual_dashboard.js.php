<script>
$(function(){
    var ctx = document.getElementById('wh_chart').getContext('2d');
    var data = <?php echo json_encode($stock_by_warehouse_data); ?>;
    
    if(data.length > 0){
        var labels = data.map(function(e) { return e.warehouse_name; });
        var values = data.map(function(e) { return e.total_units; });
        var colors = ['#28b8da', '#84c529', '#f3752d', '#d81b60', '#6c757d', '#03a9f4', '#ff6f00'];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors.slice(0, values.length)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom' }
            }
        });
    }
});
</script>