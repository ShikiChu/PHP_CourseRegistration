<?php  
include_once "Functions.php";    // __PHP_Incomplete_Class , defined the class in question before calling session_start; if you don't, PHP's session handler won't know how to deserialise the instances of that class, and you'll end up with the __PHP_Incomplete_Class Object.
include_once "EntityClassLib.php";
include ('./Common/Header.php');
extract($_POST);
if(!isset( $_SESSION['user']))
{
    header("Location: Login.php");
    exit();
} 
$errorMsg="";
$user = $_SESSION['user'];


 if(isset($selectedTerm) && $selectedTerm!="-1") // if the dropdown list change
    {
        $_SESSION['selectedTerm']=$selectedTerm;
        $errorMsg="";
         if(isset($_SESSION['selectedTerm']))
        {
            $selectedTerm = $_SESSION['selectedTerm'];
            $totalHours = getTotalHours($user->getUserId(), $selectedTerm); // get the weekly hours from db, return int
            $_SESSION['totalHours']=$totalHours;
        }
    } 
    else
    {
        $_SESSION['totalHours']=0; // if not term is selected
    }
    
 if(isset($_SESSION['selectedTerm']))
{
    $selectedTerm = $_SESSION['selectedTerm'];
}

 
if(isset($submitForm))
    {
        if (isset($selectedCourse))
        { 
            if(isset($_SESSION['totalHours']))
            {
                $totalRegisteredHours = $_SESSION['totalHours']; // accumulate the hours if it exists
            }
            //Counting the number of hours student is trying to register for
            foreach ($selectedCourse as $row)
            {
                $myPdo = getPDO();
                $sql = "SELECT WeeklyHours FROM Course WHERE CourseCode = :courseCode";
                $pStmt = $myPdo->prepare($sql);
                $pStmt->execute([':courseCode' => $row]);
                $courseHours = $pStmt->fetch();
                $totalRegisteredHours = $totalRegisteredHours + $courseHours[0]; //total of hours user is trying to register for
            }

            if ($totalRegisteredHours <= 16) //register for courses
            {
                foreach ($selectedCourse as $row)
                {
                    $userId = $user->getUserId();
                    $myPdo = getPDO();
                    $sql = "INSERT INTO registration VALUES (:StudentID, :CourseCode, :SemesterCode)";
                    $pStmt = $myPdo->prepare($sql); 
                    $pStmt->execute(array( ':StudentID'=> $userId , ':CourseCode' => $row, ':SemesterCode' => $selectedTerm));
                }
                $_SESSION['totalHours'] = $totalRegisteredHours; 

            }
            else 
            {
                $errorMsg = "Your selection exceeds the maximum weekly hours!";
            }
        }
        else 
        {
            $errorMsg = "You need to select at least one course!";
        }   
    }
    
    if(isset($clear))
    {
        header("Location: CourseSelection.php");
    }

?>


<div class="container">
    <h1>Course Selection</h1>
        <form method='post' action=CourseSelection.php>   
        <br>
        <br>    
        <h4>&nbsp &nbsp Welcome <b><?php print $user->getName();?></b>! (Not you? Change your session <a href='Logout.php'>here</a>).</h4>
        <h4>&nbsp &nbsp Your have registered <b><?php echo isset($_SESSION['totalHours']) ? $_SESSION['totalHours'] : "0"  ?></b> hours for the selected semester. </h4>
        <h4>&nbsp &nbsp Your can register <b><?php echo isset($_SESSION['totalHours']) ? 16-$_SESSION['totalHours'] : "16" ?></b> more hour(s) of courses for the semester. </h4>
        <h4>&nbsp &nbsp Please note that the courses you have registered will not be displayed in the list. </h4>        
        <br>
        <br>
        <div class="row">
            <div class="dropdown col-md-12 text-center">
                <div class="dropdown btn-group"> 
                    Term:                     
                    <select name='selectedTerm'  onchange="this.form.submit()">   
                    <option value='-1'>Select...</option>
                    <?php            
                    $termsData = getSemester();
                    
                    foreach ($termsData as $term)
                    { 
                        echo"<option value='$term[0]'";
                                if(isset($selectedTerm))
                                {
                                    if($term[0]==$selectedTerm)
                                    {
                                        echo"selected='selected'";
                                    }
                                }
                            echo">$term[1] $term[2]</option>";
                    } 
                    ?>
                </select>                
                </div>
            </div>
        </div>    

        <!-- table Contents-->  
        <div class='col-lg-4' style='color:red'> <?php echo $errorMsg;?></div><br>        
        <br><br><table class="table">
            <thead>
                <tr>
                    <th scope="col">Code</th>
                    <th scope="col">Course Title</th>
                    <th scope="col">Hours</th>
                    <th scope="col">Select</th>
                </tr>
            </thead>              
            <tbody>
                <?php
                if (isset($selectedTerm) && $selectedTerm != "-1") 
                {
                    $semester = $selectedTerm ;
                    $courseBySem = getCourseBySemeter($semester);// return CourseCode, Title, Hours from DB query
//                    foreach ($courseBySem as $item) //from the variable, print
//                    {
//                        echo "<tr>";
//                        echo "<td scope='col'>".$item[0]."</td>"; // Course Code
//                        echo "<td scope='col'>".$item[1]."</td>"; // Course Title
//                        echo "<td scope='col'>".$item[2]."</td>"; // Hours
//                        echo "<td scope='col'><input type='checkbox' name='selectedCourse[]' value='$item[0]' /></td>"; // course code 
//                        echo "</tr>";                        
//                    }
                                    
                    foreach ($courseBySem as $var) //from the variable, print
                    {
                        $studentId = $user->getUserId();
                        $code = $var[0];
                        $courseHasBeenSelected = getCourseSelected($studentId, $code); // check from Registration table, return one course that has already registrated.
                        //var_dump($courseHasBeenSelected);

                        if ($courseHasBeenSelected != $var[0])
                        {                        
                            echo "<tr>";
                            echo "<td scope='col'>".$var[0]."</td>"; // Course Code
                            echo "<td scope='col'>".$var[1]."</td>"; // Course Title
                            echo "<td scope='col'>".$var[2]."</td>"; // Hours

                            // Checkbox, value = Course Code
                            echo "<td scope='col'><input type='checkbox' name='selectedCourse[]' value='$var[0]' ";
                            if(isset($selectedCourse) && $errorMsg=="Your selection exceeds the maximum weekly hours!")
                                {
                                    if(in_array($var[0], $selectedCourse))
                                    {
                                        echo"checked";
                                    }
                                    
                                }
                            echo "/></td>"; 
                            echo "</tr>";
                        }                        
                   }                     
                }             
            ?>
               
            </tbody>  
          </table> 
                 
        <br>
        <br>
        <div class='form-group row pull-right col-lg-5'>                
            <button type='submit' name='submitForm' class='btn btn-primary col-lg-2'>Submit</button>
            <div class='col-lg-5'>
                <button type='submit' name='clear' class='btn btn-primary col-lg-5'>Clear</button>
            </div>
        </div>  
        <br><br><br>
    </form>
</div>
<?php include ('./Common/Footer.php'); ?>