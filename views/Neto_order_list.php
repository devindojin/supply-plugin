
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css"/>
<script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.js"/> </script>
<script  type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"/></script>
<div class="container">
    <div class=""><!---- START AJAX LOAD ---->
        <!---- END AJAX LOAD ---->
        <h2>Neto Standard Orders</h2>
        <hr />
        <form  method="post" name="itemForm" id="itemForm">
            <div class="clear"></div>
            <div class="filter-results">
                <button type="submit" class="btn btn-success">Order Status : Pick </button>
                <button type="submit" class="btn btn-danger">Sync Orders </button>                
            </div><br>
            <div>
                <?php 
                if(isset($alerts)){
                    foreach ($alerts as $alert)
                    {echo $alert;}
                    }
                ?>
            </div>
            <div class="widget widget-table">
                <div class="widget-content" id="ajax-content-pl">
                    <br>
                    <!---- START AJAX LOAD ---->
                    <table class="table table-striped table-bordered display" id="listorder">
                        <thead>
                            <tr>
                                <th>Date Placed</th>
                                <th>Invoice ID</th>
                                <th>Recipient</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr></thead>
                        <tbody>
                            <?php                            
                           
                            if(!isset($orders_got['data'])){
                                 $neto_order = array();
                            }else{
                                $neto_order = $orders_got['data'];
                            } 
                            foreach ($neto_order as $n_order) {
                                ?>
                                <tr>
                                    <td nowrap><?php echo $n_order['DateInvoiced']; ?></td>
                                    <td><a href="#" onClick="return targetopener(this)"><?php echo $n_order['OrderID']; ?></a></td>
                                    <td nowrap><?php echo $n_order['ShipFirstName'] . ' ' . $n_order['ShipLastName']; ?></td>
                                    <td nowrap><?php echo $n_order['Email']; ?></td>
                                    <td nowrap>
                                        <button class="btn btn-success proceed_ord">
                                            <span hidden="hidden"><?php echo $n_order['OrderID']; ?></span>  Process
                                        </button>
                                    </td>

                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <input type="hidden" name="itm_total" value="50">
                    <!---- END AJAX LOAD ---->
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    jQuery(document).ready(function () {
        
        jQuery('#listorder').DataTable();

        jQuery('.proceed_ord').on('click', function (e) {
            e.preventDefault();
            let nid = jQuery(this).find('span').html();
            //window.location.href = '<?php echo admin_url() . 'admin.php?page=process_Order' ?>';
            window.location.href = '<?php echo admin_url() . 'admin.php?page=process_Order&NetoId=' ?>' + nid;
        });
    });
</script>




