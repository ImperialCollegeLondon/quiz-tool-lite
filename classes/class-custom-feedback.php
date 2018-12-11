<?php
add_filter( 'post_updated_messages', 'pot_updated_messages', 10, 1 );	
	
	
function pot_updated_messages ( $msg )
{
	$msg[ 'ek_pot' ] = array (
	0 => '', // Unused. Messages start at index 1.
	1 => "Question Pot updated.",
	2 => 'Custom field updated.',  // Probably better do not touch
	3 => 'Custom field deleted.',  // Probably better do not touch
	4 => "Question Pot updated.",
	5 => "",
	6 => "Question Pot Created.",
	7 => "Question Pot saved.",
	8 => "",
	9 => "",
	10 => "",
	);
	return $msg;
}
?>