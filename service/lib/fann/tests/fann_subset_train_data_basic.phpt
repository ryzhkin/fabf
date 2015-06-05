--TEST--
Test function fann_subset_train_data() by calling it with its expected arguments
--FILE--
<?php

$filename = ( dirname( __FILE__ ) . "/fann_subset_train_data.tmp" );
$content = <<<EOF
4 2 1
-1 -1
-1
-1 1
1
1 -1
1
1 1
-1
EOF;

file_put_contents( $filename, $content );
$train_data = fann_read_train_from_file( $filename );

$copy_train_data = $train_data;
$subset_train_data = fann_subset_train_data( $train_data, 1, 2 );

var_dump( $subset_train_data );
var_dump( $copy_train_data == $train_data );
var_dump( $subset_train_data == $train_data );

?>
--CLEAN--
<?php
$filename = ( dirname( __FILE__ ) . "/fann_subset_train_data.tmp" );
if ( file_exists( $filename ) )
	unlink( $filename );
?>
--EXPECTF--
resource(%d) of type (FANN Train Data)
bool(true)
bool(false)