<?php
	//
	// FILE :  yui_dt_srv.php
	//
	//  ( YOUR INTERNAL SERVER DB Libraries will go here 
	//     ... mine are not shown! )
	
	//
	//  Also, one of my server libraries includes a php function "db_lookup" that takes 
	//  in a SQL string, processes the MySQL function, and returns an array of record 
	//  objects using mysql_fetch_assoc.  
	//
	//  You should be familiar enough with php to write this function yourself ...!
	//
	
	//
	//  Define the server response DEFAULTS
	//  NOTE : This server is expecting "POST" Method was used, "GET" will not work! 
	//		
		$reply_code 	= 401;
		$srv_name   	= '[' . str_ireplace('.php','',basename($_SERVER['SCRIPT_NAME'])) . ']'; 
		$reply_text 	= "$srv_name No option selected!";  
	
	//
	//  Process the incoming SERVER POST parameters ... from YUI request
	//
		$input_opt 	 	= mysql_escape_string( $_POST['iopt'] );
		$input_key   	= mysql_escape_string( $_POST['ipk'] );
		$input_data  	= mysql_escape_string( $_POST['idata'] );
		$input_param	= mysql_escape_string( $_POST['iparam'] );
	
		$pkn 			= mysql_escape_string( $_POST['pk_name'][0] );
		$pkv 			= mysql_escape_string( $_POST['pk_value'][0] );
	
	//
	//  Now do the specific request 
	//
		switch ( $input_opt ) {
			
			case 'data_custs':
	
				$lwhere = ( $pkn && $pkv>0 ) ? " AND " . $pkn . "=" . $pkv : " ";
			
			
				$lsql = "SELECT
							cust_id, cust_name, cust_location, cust_status, 
							COUNT(ord_id) as num_orders, SUM(ord_total) as sum_orders    
						 FROM 
						 	TEST_customers 
							LEFT JOIN TEST_orders ON (ord_custid=cust_id)								   
							WHERE 1 $lwhere 
							GROUP BY cust_id 
							ORDER BY cust_id ASC";
				
				$drows = db_lookup( $lsql );
					
				if ( $drows !== false ) {
					$rows = array();
					foreach( $drows as $drow )
						$rows[] = array_map("stripslashes",$drow);
							
					$reply_code = 200;
					$reply_text = "Data Follows";			
						
				} else {
					$reply_code = 503;
					$reply_text = "$srv_name:$input_opt Error : Could not retrieve data for $mod_name after $reply_mode ! SQL=$lsql";
				}
					
				break;
								
			
			
			case 'data_orders':
	
				$lwhere = " ";
				$lwhere = " AND " . $input_key . "=" . $input_data;
			
				$lsql = "SELECT
							ord_id, ord_custid, ord_title, ord_date, ord_total, ord_status    
						 FROM 
						 	TEST_orders    
						 WHERE 1 $lwhere";
				
				$drows = db_lookup( $lsql );
					
				if ( $drows !== false ) {
					$rows = array();
					foreach( $drows as $drow )
						$rows[] = array_map("stripslashes",$drow);
							
					$reply_code = 200;
					$reply_text = "Data Follows";			
						
				} else {
					$reply_code = 503;
					$reply_text = "$srv_name:$input_opt Error : Could not retrieve data for $mod_name after $reply_mode ! SQL=$lsql";
				}
							
				break;
	
				
				
			case 'data_order_details':
	
				$lwhere = " ";
				$lwhere = " AND " . $input_key . "=" . $input_data;
			
				$lsql = "SELECT
							od_id, od_ordid, od_item, od_desc, od_qty, od_price, od_total     
						 FROM 
						 	TEST_order_details    
						 WHERE 1 $lwhere";
				
				$drows = db_lookup( $lsql );
					
				if ( $drows !== false ) {
					$rows = array();
					foreach( $drows as $drow )
						$rows[] = array_map("stripslashes",$drow);
							
					$reply_code = 200;
					$reply_text = "Data Follows";			
						
				} else {
					$reply_code = 503;
					$reply_text = "$srv_name:$input_opt Error : Could not retrieve data for $mod_name after $reply_mode ! SQL=$lsql";
				}
							
				break;
				
				
			default:
				$rows = "No Server Option was selected!";			
		}
	
	
	//
	//  Done processing, the returned data is in  $rows as an array [rows][objects]
	//
	   $returnValue = array(
	        'replyCode' => $reply_code,
	        'replyText' => $reply_text,
	        'Results' => $rows
	    );
	
	    $json = new Services_JSON();
	    $ret = $json->encode($returnValue); 
	
	
	//--------------
	//   Output the JSON data
	//--------------
	//	header("Content-Type: application/json");   // This is commented out during development so I can see response on-screen
		header("Cache-Control: no-cache");
	
		echo $ret;
	
?>