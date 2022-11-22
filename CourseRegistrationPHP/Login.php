<?php include ('./Common/Header.php'); 
include_once "Functions.php";
include_once "EntityClassLib.php";
extract($_POST);
$studentIdErr=$passwordErr=$loginErrorMsg="";

if(isset($login))
{
    if(empty($studentID))
    {
        $studentIdErr="Student ID is not blank";
    }
    
    if(empty($password))
    {
        $passwordErr="Password is not blank";
    }
    else  
    {
        $password = hash("sha256", $password); // encrypted password match the one in registration page
        $_SESSION['studentID'] = $studentID;
    }
    
    if($studentIdErr=="" && $passwordErr=="")
    {
        try 
        {
            $user = getUserByIdAndPassword($studentID, $password);
        }
        catch (Exception $e)
        {
            die("The system is currently not available, try again later"); // print msg and terminate the script
        }

        if ($user == null)
        {
            $loginErrorMsg = 'Incorrect student ID and/or Password!';
        }
        else 
        {
            $_SESSION['user'] = $user; 
            header("Location: CourseSelection.php");
            exit();
        }
    }   
}

if(isset($clear))
{
    header("Refresh:0; url=Login.php"); 
}

?>


<div class="container">
    <div class="col-md-6">
        <h2 class="text-center">Login</h2>
        <p>*You need to <a href="NewUser.php" >sign up </a>if you are a new user.*</p>
        <div class="validationErr"><?php echo $loginErrorMsg?></div>
    </div>   
</div>
    
    
<form class ="form-horizontal" method="post">
    <div class="form-group form-group-lg">
        <label class="col-md-2 control-label" for="studentID">Student ID:</label>
            <div class="col-md-3">
                <input type="text" class="form-control" name="studentID" value="<?php print(isset($studentID)?$studentID:'');?>">
            </div>
        <div class="validationErr"><?php echo $studentIdErr?></div>
    </div>

    <div class="form-group form-group-lg">
        <label class="col-md-2 control-label" for="passward">Password:</label>
            <div class="col-md-3">
                <input type="password" class="form-control" name="password" value="" >
                <br>
                <button type="submit" class="btn btn-success" name="login">Login</button>
                <input type="submit" value="Clear" name="clear" class="btn btn-success">
            </div>
        <div class="validationErr"><?php echo $passwordErr?></div>
    </div>

</div>
</form>
<?php include ('./Common/Footer.php'); ?>