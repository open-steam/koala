<?php

include_once( "Spreadsheet/Excel/Writer.php" );

class lms_excel
{
	
	private function get_members( $pGroup )
	{
		
	}
	
    public function group_get_members( $pGroup )
    {
        if ( ! $pGroup instanceof koala_group )
        {
            throw new Exception( "parameter is not an instance of koala_group", E_PARAM );
        }
         
        if ( ! $pGroup->is_admin( lms_steam::get_current_user() ) )
        {
            throw new Exception( "no admin rights.operation canceled." );
        }
         
        $cache = get_cache_function( $pGroup->get_id(), CACHE_LIFETIME_STATIC );
         
        switch( get_class( $pGroup ) )
        {
            case( "koala_group_course" ):
                $group_name = $pGroup->get_course_id() . " - " . $pGroup;
                $members = $cache->call( "lms_steam::group_get_members", $pGroup->steam_group_learners->get_id() );
                break;

            default:
                $members = $cache->call( "lms_steam::group_get_members", $pGroup->get_id() );
                break;
        }
         
         
        // INITIALIZATION
         
        $course_id    = $pLmsGroupCourse->get_course_id();
        $course_name  = $pLmsGroupCourse->get_course_dsc_short();
        $semester     = $pLmsGroupCourse->get_semester();
         
        $excel        = new Spreadsheet_Excel_Writer();
        $excel->send( $course_id . "_" . $semester->get_name() );
        $sheet        =& $excel->addWorksheet( gettext( "participants" ) );
         
        // WRITE EXCEL SHEET
        $sheet->writeString( 0, 0, $course_id . " - " . $course_name() );
        $sheet->writeSting(  1, 0, $semester->get_name() );
        $sheet->writeString( 3, 0, gettext( "student id" ) );
        $sheet->writeString( 3, 1, gettext( "forename" ) );
        $sheet->writeString( 3, 2, gettext( "surname" ) );
         
        $no_members = count( $members );
        if ( $no_members > 0 )
        {
            $row = 5;
            for( $i = $start; $i < $end; $i++ )
            {
                $member = $members[ $i ];
                 
                $sheet->writeString( $row, 1, $member[ "USER_FIRSTNAME" ]);
                $sheet->writeString( $row, 2, $member[ "USER_FULLNAME" ] );
                $sheet->writeString( $row, 3, $member[ "USER_EMAIL" ] );
                $sheet->writeString( $row, 4, $member[ "USER_PROFILE_FACULTY" ] );
                $row++;
            }
        }
        $excel->close();
    }

}

?>
