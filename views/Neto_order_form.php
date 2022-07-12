<style>
    .intro {
        font-size: 150%;
        color: red;
    }

    div#form_new p span {
        float: right;
    }
    div#form_new {
        width: 65%;
        margin: 0px auto;
        background: #e8e8e8;
        padding: 20px; 
        border: 1px solid #000;
        margin-top: 10px;
    }
    #form_new .button {
        background: #fff;
        border: 1px solid #9a9898;
        border-radius: 5px;
        font-size: 11px;
        padding: 0px 5px;
        margin-right: 5px;
        line-height: 0;
        height: 22px;
        color: #000;
    }
    body{
        color: #000;
        font-size: 13px;
    }
    #form_new .number-field input[type="text"], .input-drop select#state{
        float: right;
        width: 70%;
        padding: 0 5px;
        height: 22px;
    }
    .part_table{
        display: inline-block;
        border: 1px solid #000;
        padding: 10px;
        width: 100%;
        margin-top: 25px;
    }
    .part_table table th {
        background: #d8d8d8;
        border: 1px solid #969696 !important;
        text-align: center;
        padding: 1px;
    }
    .part_table table td {
        background: #fff;
        border: 1px solid #969696 !important;
        text-align: center;
        padding: 1px;
        padding: 3px 3px;
    }
    .part_table input[type="text"]{
        width: 100%;
    }
    div#form_new p span {
        float: right;
        font-weight: 700;
    }.code {
        width: 50%;
        float: right;
        margin: 1em 0;
    }.good p {
        width: 50%;
        float: left;
        font-weight:700;
    }.code span input[type="text"] {
        width: 100%;
    }.number-field span {
        float: left;
    }.input-drop {
        float: left;
        width: 100%;
        padding: 4px 0px;
    }.code span {
        font-weight: 700;
    }
    .number-field span {
        float: left;
        font-weight: 700;
    }.input-droper span {
        font-weight: 700;
    }

    .number-field input[type="text"] {
        float: right;
    }
    .col-md-6 {
        float:left;
    }.group-store h4 {
        font-size: 1.25rem;
        padding-top: 9px;
    }
    .input-drop select#state {
        float: right;
    }
    .number-field {
        width: 100%;
        float: left;
    }.change-roll {
        width: 100%;
        float: left;
    }
    .change-roll input[type="text"] {
        width: 100%;
    }
    .change-roll select {
        width: 50%;
    }
    .input-droper strong {
        color: #f00;
        font-weight: 500;
        float: right;
        padding: 0px 5px;
        font-size: 15px;
    }
    .input-droper {
        padding: 4px 0px;
    }
    input[type=checkbox], input[type=radio] {
        margin: 4px 8px 8px;
        margin-top: 1px\9;
        line-height: normal;
    }
</style>


<?php

    $order_data = $orders_got['data'][0];   
    $qty = $Qty_to_get;
    $sku;
    $sic_arr = explode(' ', $sic);     
?>
<div id="neto_msg"></div>
<div id="form_new">	  
    <h3>RDA-online order form</h3>  
    <p><span>Click here to upload Ship Docs</span></p>
    <form method="post" action="" enctype="multipart/form-data">
        <button type="submit" class="button" name="sub_order">SUBMIT ORDER</button>
        <input type="reset" class="button" value="CLEAR ORDER" />

        <input type="file" class="button pull-right" name="file"/>

        <div class="good">
            <br>
            <p>Customer code<br>EMAP
            </p>
            <div class="code">
                <span>
                    PO Number* limited to 15 characters<br>
                    <input readonly type="text" name="order_id" value="<?php echo $order_data['OrderID']; ?>">
                </span>
            </div>
        </div>
        <div class="">
            <input type="radio" name="Delivery" value="Delivery" checked="">Delivery 
            <input type="radio" name="Delivery" value="Pickup" > Pick up<br>
        </div>
        <div class="group-store">
            <div class="col-md-6">
                <h4>Ship from:</h4>
                <select name="Ship_from" required>
                    <option value="">---- Select option ---</option>
                    <option value="WANGARA">WANGARA</option>
                    <option value="TULLAMARINE">TULLAMARINE</option>
                    <option value="BULLEEN">BULLEEN</option>
                    <option value="ARNDELL PARK">ARNDELL PARK</option>
                    <option value="KEYSBOROUGH">KEYSBOROUGH</option>
                    <option value="BRISBANE">BRISBANE</option>
                    <option value="BANKSTOWN">BANKSTOWN</option>
                    <option value="ADELAIDE">ADELAIDE</option>
                    <option value="NEWCASTLE">NEWCASTLE</option>
                    <option value="TOWNSVILLE">TOWNSVILLE</option>
                    <option value="PERTH">PERTH</option>
                </select>
            </div>
            <div class="col-md-6">
                <div class="number-field">
                    <h5>Destination</h5>
                    <div class="input-drop"><span>Name : </span><input  type="text" name="name"  value="<?php echo $order_data['ShipFirstName'].' '.$order_data['ShipLastName']; ?>" /><br></div>
                    <div class="input-drop"><span>Address:</span> <input  type="text" name="ShipStreetLine1" value="<?php echo $order_data['ShipStreetLine1']; ?>" /><br></div>  
                    <div class="input-drop"><span>Address : </span><input  type="text" name="ShipStreetLine2" value="<?php echo $order_data['ShipStreetLine2']; ?>"/><br></div>
                    <div class="input-drop"><span>Suburb : </span><input readonly type="text" name="Suburb" value="<?php echo $order_data['ShipCity']; ?>" /><br></div>
                    <div class="input-drop"><span>State : </span><input readonly type="text" name="state"  value="<?php echo $order_data['ShipState']; ?>" ><br>      
                    </div>
                    <div class="input-drop"><span>Postcode : </span><input readonly type="text" name="Postcode" value="<?php echo $order_data['BillPostCode']; ?>" /><br></div>
                    <div class="input-drop"><span>Phone : </span><input  type="text" name="Phone" value="<?php echo $order_data['ShipPhone']; ?>"/><br></div>
                </div>
            </div>
        </div>

        <div class="change-roll">
            <div class="input-droper"><span>Special Instructions : <strong>*Not Shown on invoice</strong></span><br> <input type="text"  name="Instructions" /><br></div>
            <div class="input-droper"><span>Comments :<strong>*Shown at bottom of invoice</strong></span><br> <input type="text"  name="Comments" /><br></div>
            <div class="input-droper" required><span>Corfirmation email :</span><br>
                <select name="mail_to" required>
                    <option value="" >--- Select email ---</option>
                    <!--<option value="avinashmishra.vll@gmail.com">demo</option>-->
					<option value="d.williams@rdabrakes.com.au">d.williams@rdabrakes.com.au</option>
					<option value="brent@777power.com">brent@777power.com</option>
                    <option value="admin@empoweredautoparts.com.au">admin@empoweredautoparts.com.au</option>
                    <option value="kevin@empoweredautoparts.com.au">kevin@empoweredautoparts.com.au</option>
                </select><br>
            </div>
        </div>

        <div class="part_table">
            <table border="1" class="table-bordered ">
                <tr>
                    <th>Part number</th>
                    <th>Qty</th>
                    <th>Part number</th>
                    <th>Qty</th>
                </tr>                
                <?php                         
                    for($i=0;$i<count($sic_arr);$i+=2){   
                         $inc = $i+1;
                        if(isset($sic_arr[$i]) && $sic_arr[$i]!=''&& $sic_arr[$i]!=' ')
                        {
                            $sic = $sic_arr[$i];                            
                        }
                        else{
                            $sic = '';
                            $qty = '';
                        }                            
                        if(isset($sic_arr[$inc])&& $sic_arr[$inc]!=''&& $sic_arr[$inc]!=' ')
                        {                                 
                            $sic_inc = $sic_arr[$inc];
                            $qty_inc = $qty;
                        }
                        else{                                
                            $sic_inc='';
                            $qty_inc='';
                        }
                        echo'<tr>';
                            echo'<td><input type="text"  name="part_number[]" value="'.$sic.'" /></td>
                                 <td><input type="text"  name="qty[]" value="'.$qty.'" /></td>
                                 <td><input type="text"  name="part_number[]" value="'.$sic_inc.'" /></td>
                                 <td><input type="text"  name="qty[]" value="'.$qty_inc.'" /></td>';
                        echo'</tr>';                            
                    }

                ?>              
                <tr><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td></tr>	
                <tr><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td></tr>	
                <tr><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td></tr>	
                <tr><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td></tr>	
                <tr><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td></tr>	
                <tr><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td></tr>	
                <tr><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td></tr>	
                <tr><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td><td><input type="text"  name="part_number[]" /></td><td><input type="text"  name="qty[]" /></td></tr>	
            
            </table>
        </div>
        <button type="submit" class="button" name="sub_order">SUBMIT ORDER</button>
        <input type="reset" class="button" value="CLEAR ORDER" />
    </form>
</div>
