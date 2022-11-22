<?php 
include_once "Functions.php";    // __PHP_Incomplete_Class , defined the class in question before calling session_start; if you don't, PHP's session handler won't know how to deserialise the instances of that class, and you'll end up with the __PHP_Incomplete_Class Object.
include_once "EntityClassLib.php";
include ('./Common/Header.php');
extract($_POST);

if(!isset( $_SESSION['user']))
{
    header("Location: Login.php");
}
$errorMsg="";
$user = $_SESSION['user'];

if(isset($submit))
    {  
        if (isset($selectedCourse))
        { 
            foreach ($selectedCourse as $item) //loop and look for what was selected
            {
                $myPdo = getPDO();
                $sql = "DELETE FROM registration WHERE registration.CourseCode = :courseCode ";  
                $pStmt = $myPdo->prepare($sql);
                $pStmt->execute(array(':courseCode' => $item));  
            }
        }
        else 
        {
            $errorMsg = "You must select at least one checkbox!";
        }                
    }


?>


<div class="container">
    <form method='post' action=CurrentRegistration.php>   
    <br><br>
    <h1>&nbsp &nbsp Current Registrations</h1>    
    <br><br>
    <h4>&nbsp &nbsp Welcome <b><?php echo $user->getName();?></b>! (Not you? Change your session <a href="Logout.php">here</a>). The following are your current registrations:</h4>


    <div class='col-lg-4' style='color:red'> <?php echo $errorMsg;?></div><br>
    <br><br><table class="table">
        <thead>
            <tr>
                <th scope="col">Year</th>
                <th scope="col">Term</th>
                <th scope="col">Course Code</th>
                <th scope="col">Course Title</th>
                <th scope="col"></th>
                <th scope="col">Hours</th>
                <th scope="col">Select</th>                                                                                
            </tr>
        </thead> 
        <tbody>

            <?php
            //Getting array with information
            $myPdo = getPDO();
            $userId = $user->getUserId();
            $sql = "SELECT semester.Year, semester.Term, Course.CourseCode, course.Title, course.WeeklyHours "
                . "FROM Course INNER JOIN Registration ON Course.CourseCode = Registration.CourseCode " 
                . "INNER JOIN courseoffer ON courseoffer.CourseCode = registration.CourseCode "
                . "INNER JOIN semester ON (courseoffer.SemesterCode = semester.SemesterCode AND semester.SemesterCode = registration.SemesterCode) "
                . "WHERE Registration.StudentID = :studendId "
                . "ORDER BY semester.Year ASC, semester.Term" ;  
            $pStmt = $myPdo->prepare($sql);
            $pStmt->execute ([':studendId' => $userId]);
            $coursesRegistered = $pStmt->fetchAll();
            $currentTerm = "";
            $currentYear = "";

            foreach ($coursesRegistered as $row)
            {
                if ($currentYear == "")
                {
                    $currentYear = $row[0];
                    $totalHours = 0;
                }  

                if ($currentTerm == "")
                {
                    $currentTerm = $row[1];                       
                    $totalHours = 0;
                }  

                if ( $currentYear != $row[0] ||  $currentTerm != $row[1]) //print empty lines
                { 
                        echo "<tr>";
                        echo "<td scope='col'></td>"; // Year
                        echo "<td scope='col'></td>"; // Term
                        echo "<td scope='col'></td>"; // CourseCode
                        echo "<td scope='col'></td>"; // CourseTitle
                        echo "<th scope='col'>Total Weekly Hours</th>"; // Blank
                        echo "<td scope='col'><b>".$totalHours."</b></td>"; // Hours
                        echo "<td></td>"; 
                        echo "</tr>";
                        //set $currentTerm to next value of Term:
                        $totalHours = 0;
                        $currentYear = $row[0]; //set year to the next year record                            
                        $currentTerm = $row[1]; //set term to next term record                        

                }     
                //print following term
                echo "<tr>";
                echo "<td scope='col'>".$row[0]."</td>"; // Year
                echo "<td scope='col'>".$row[1]."</td>"; // Term
                echo "<td scope='col'>".$row[2]."</td>"; // CourseCode
                echo "<td scope='col'>".$row[3]."</td>"; // CourseTitle
                echo "<td scope='col'></td>"; // Blank
                echo "<td scope='col'>".$row[4]."</td>"; // Hours
                // Checkbox, value = Course Code
                echo "<td scope='col'><input type='checkbox' name='selectedCourse[]' value='$row[2]' /></td>"; 
                echo "</tr>";  
                $totalHours = $totalHours + $row[4];
            }
            //printing last line
            echo "<tr>";
            echo "<td scope='col'></td>"; // Year
            echo "<td scope='col'></td>"; // Term
            echo "<td scope='col'></td>"; // CourseCode
            echo "<td scope='col'></td>"; // CourseTitle
            echo "<th scope='col'>Total Weekly Hours</th>"; // Blank
            echo "<td scope='col'><b>".$totalHours."</b></td>"; // Hours
            echo "<td></td>"; 
            echo "</tr>";
            ?> 
        </tbody>
    </table> 

    <br><br><div class='form-group row'>  
            <div class="col-md-6">
                <button type='submit' name='submit' class='btn btn-primary' onclick='return confirm("The selected registration will be deleted!")'>Delete Selected</button>
                &nbsp; &nbsp;
                <button type='submit' name='clear' class='btn btn-primary'>Clear</button>
            </div>
    </div><br><br>            
    </form> 
</div>
<?php include ('./Common/Footer.php'); ?>