<HTML>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <HEAD>
    <TITLE>PHP-Test</TITLE>
    <BODY>
      <center>
        <P>&nbsp;</P>
        <P>&nbsp;</P>
        <P>
          <b>koaLA Konvertierungsscript für die Kompatibilität der Erweiterungen</b>
        </P>
        <P>&nbsp;</P>
        <?php
          include("../../etc/koala.def.php");
          include("../../classes/PHPsTeam/steam_connector.class.php");
          $aktsem = (isset($_GET["semester"])?$_GET["semester"]:STEAM_CURRENT_SEMESTER);
          $connector = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
          
          $GLOBALS["STEAM"] = $connector;

          $scg = steam_factory::get_object( $connector->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );
          $scg = steam_factory::get_object( $connector->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );
          $semname = $scg->get_groupname() . "." . $aktsem;
          $semester = steam_factory::groupname_to_object( $connector->get_id(), $semname );
          if (!is_object($semester)) {
            die("Cannot find Group " . $scg->get_groupname() . "." . $aktsem . " for semester " . $aktsem );
          }
          $courses = $semester->get_subgroups();
          steam_factory::load_attributes($connector->get_id(), $courses, array( OBJ_DESC, OBJ_NAME, "COURSE_UNITS_ENABLED" ));
          echo "Listing all Courses for Semester " . $aktsem . " (" . $semname . ")";
          echo("<table><tr><td>Kurs</td><td>COURSE_UNITS_ENABLED</td><td>UNITS_DOCPOOL_ENABLED</td><td>Konvertiert</td></tr><tr>");
          foreach($courses as $course) {
            if (is_object($course)) {
              echo "<tr><td>" . $course->get_name() . " (" . $course->get_attribute(OBJ_DESC) . ")</td><td>" . $course->get_attribute("COURSE_UNITS_ENABLED") .
              "</td><td>" . $course->get_attribute("UNITS_DOCPOOL_ENABLED") . "</td><td>";
              if ($course->get_attribute("COURSE_UNITS_ENABLED") === "TRUE" && $course->get_attribute("UNITS_DOCPOOL_ENABLED") !== "TRUE"  ) {
                if (isset($_GET["write"]) && $_GET["write"] == "true") 
		{
		  //$course->set_attribute("COURSE_UNITS_ENABLED", "TRUE");
		  $course->set_attribute("UNITS_DOCPOOL_ENABLED", "TRUE");
		}
                echo("TRUE");
              }
              
              echo "</td></tr>";
              
            }
          }
          echo("</tr></table>");
          echo("<br />Requestcount: " . $connector->get_request_count() . "<br />\n\r");
          $connector->disconnect();
        ?>
      </p>
    </center>
  </BODY>
</HTML>
