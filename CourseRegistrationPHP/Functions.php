 <?php
 
 function getPDO()
{
    $dbConnection = parse_ini_file("DBConnection.ini"); //read the ini file
    extract($dbConnection);
    return new PDO($dsn, $scriptUser, $scriptPassword);  // create the PDO obj
}

 function getTotalHours($userId,$selectedTerm)
{
    $pdo = getPDO();
    $sql = "SELECT Course.CourseCode CourseCode, Title, WeeklyHours "
            . " FROM Course INNER JOIN Registration "
            . " ON Course.CourseCode = Registration.CourseCode "
            . " INNER JOIN semester ON registration.SemesterCode = semester.SemesterCode "
            . " WHERE Registration.StudentID = :studendId AND semester.SemesterCode = :semesterCode ";
    $pStmt = $pdo->prepare($sql);
    $pStmt->execute (array(':studendId' => $userId, ':semesterCode' => $selectedTerm));
    $courseById = $pStmt->fetchAll();
    $totalRegisteredHours = 0;
    foreach ($courseById as $row)
    {
        $totalRegisteredHours = $totalRegisteredHours + $row[2];
    }
    return $totalRegisteredHours;
}

function getSemester()
{
    $pdo = getPDO();
    
    $sql = "SELECT * FROM Semester ";
    $pStmt = $pdo->prepare($sql); 
    $pStmt->execute();
    
    foreach ($pStmt as $row)
    {
         $term = array( $row['SemesterCode'], $row['Year'], $row['Term']);
         $termsArray[] = $term;
    }
    return $termsArray;
}

function getCourseSelected($userId, $courseCode)
{
    $myPdo = getPDO();
    $sql = "SELECT CourseCode "
            . "FROM Registration "
            . "WHERE StudentId = :studentId AND CourseCode = :courseCode";
    $pStmt = $myPdo->prepare($sql);
    $pStmt -> execute([':studentId' => $userId, ':courseCode' => $courseCode]);
    $courseHasBeenSelected = $pStmt->fetchColumn();
    return $courseHasBeenSelected;
}

function getRegistrationHours($userId)
{
    $myPdo = getPDO();
    $sql = "SELECT SUM(course.WeeklyHours) "
            ."FROM registration INNER JOIN course ON registration.CourseCode = course.CourseCode "
            ."WHERE registration.StudentId= :studentId";
    $pStmt = $myPdo->prepare($sql);
    $pStmt ->execute(['studentId'=> $userId]);
    $totalHours = $pStmt->fetchColumn();
    return $totalHours;
}

function getCourseBySemeter($semeter)
        {
            $pdo = getPDO();

            $sql = "SELECT Course.CourseCode Code, Title,  WeeklyHours "
                       ."FROM Course INNER JOIN CourseOffer ON Course.CourseCode = CourseOffer.CourseCode "
                       ."WHERE CourseOffer.SemesterCode = :semesterCode";
            $pStmt = $pdo->prepare($sql);
            $pStmt->execute( [ 'semesterCode' => $semeter ] );

            foreach ($pStmt as $row)
            {
                $course = array( $row['Code'], $row['Title'], $row['WeeklyHours'] );
                $courses[] = $course;
            }
            return $courses;
        }

function IsExistID($userId)
{
    
//    $conn = mysqli_connect("localhost", "PHPSCRIPT", "1234", "Lab5");
//    $result = mysqli_query($conn,"SELECT StudentId FROM Student WHERE StudentId = '$userId'");
//    if(mysqli_num_rows($result)>0)
//    {
//        return mysqli_num_rows($result);
//    } 
//    else 
//    {
//        return null;
//    }
    
    // prepared statement below
    $pdo = getPDO();
    $sql = "SELECT StudentId FROM Student WHERE StudentId = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['userId'=> $userId]);
    $CheckAcc = $stmt->rowCount();
    if($CheckAcc > 0 )
    {
        return $CheckAcc;
    }
    else 
    {
        return null;
    }

}

function getUserByIdAndPassword($studentID, $password)
{
    $pdo = getPDO();

    $sql = "SELECT StudentId, Name, Phone FROM Student WHERE StudentId = :userId AND Password = :password";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['userId' => $studentID, 'password' => $password]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row)
    {
        return new User($row['StudentId'], $row['Name'], $row['Phone'] ); 
    }
    return null;

//$sql = "SELECT StudentId, Name, Phone FROM Student WHERE StudentId = '$userId' AND Password = '$password'";
//
//    $resultSet = $pdo->query($sql);
//    if ($resultSet)  // if the sql statement is valid 
//    {
//        $row = $resultSet->fetch(PDO::FETCH_ASSOC);// PDO::FETCH_ASSOC returns indexed array 
//        if ($row)
//        {
//          return new User($row['UserId'], $row['Name'], $row['Phone'] );            
//       }
//      else
//       {
//          return null;
//      }
//  }
//  else
//  {
//        throw new Exception("Query failed! SQL statement: $sql");
//    }
}

function addNewUser($userId, $name, $phone, $password)
{
   $pdo = getPDO();
     
    //$sql = "INSERT INTO Student (StudentId, Name, Phone, Password) VALUES( '$userId', '$name', '$phone', '$password')";
    //$pdoStmt = $pdo->query($sql);
    
    $sql = "INSERT INTO Student VALUES( :studentId, :name, :phone, :password)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['studentId' => $userId, 'name' => $name, 'phone' => $phone, 'password' => $password]);
}



