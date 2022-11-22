<?php 
include ('./Common/Header.php');
include_once "Functions.php";
include_once "EntityClassLib.php";

$studentIdErr=$nameErr=$phoneErr=$passwordErr=$passwordCheckErr="";
$regexPwd = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{6,}$/";
$regexPhone = '/\b[0-9]{3}-[0-9]{3}-[0-9]{4}$/';

extract($_POST);
if(isset($submit))
{
    if(empty($studentID))
    {
        $studentIdErr="Student ID is not blank";
    }elseif (IsExistID($studentID) > 0) 
    {
        $studentIdErr="A student with this ID has already signed up";
    }
    
    if(empty($name))
    {
        $nameErr="Name is not blank";
    }
    
    if(empty($phone))
    {
        $phoneErr="Phone is not blank";
    }
    elseif(!preg_match($regexPhone,$phone ))
    {
        $phoneErr="Phone Number must be in the format of nnn‐nnn‐nnnn";
    }
    
    if(empty($password))
    {
        $passwordErr="Password is not blank";
    }
    elseif(!preg_match($regexPwd,$password ))
    {
        $passwordErr="Password must be at least 6 characters long, contains at least one upper case, one 
lowercase and one digit. ";
    } 
    
    if(empty($passwordCheck))
    {
        $passwordCheckErr="You must enter to check your password";
    }
    elseif ($passwordCheck != trim($password)) 
    {
        $passwordCheckErr="Your password doesn't match!";
    }
    else 
    {
        $password = hash("sha256", $passwordCheck);
    }
    
    if($studentIdErr==""&&$nameErr==""&&$phoneErr==""&&$passwordErr==""&&$passwordCheckErr=="")
    {
        addNewUser($studentID, $name, $phone, $password);
        $user = getUserByIdAndPassword($studentID, $password);
        $_SESSION['user']=$user;
        header("Location: CourseSelection.php");
        exit();
    }
}

if(isset($clear))
{
    header("Refresh:0; url=NewUser.php"); 
}
?>


<div class="container">
    <div class="col-md-6">
        <h2 class="text-center">Sign Up</h2>
        <p class="text-center">*All fields are required*</p>
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
        <label class="col-md-2 control-label" for="name">Name:</label>
            <div class="col-md-3">
                <input type="text" class="form-control" name="name" value="<?php print(isset($name)?$name:'');?>">
            </div>
        <div class="validationErr"><?php echo $nameErr?></div>
    </div>

    <div class="form-group form-group-lg">
        <label class="col-md-2 control-label" for="phone">Phone:</label>
            <div class="col-md-3">
                <input type="text" class="form-control" name="phone" value="<?php print(isset($phone)?$phone:'');?>" placeholder="nnn-nnn-nnnn">
            </div>
        <div class="validationErr"><?php echo $phoneErr?></div>
    </div>

    <div class="form-group form-group-lg">
        <label class="col-md-2 control-label" for="passward">Password:</label>
            <div class="col-md-3">
                <input type="password" class="form-control" name="password" value="" >
            </div>
        <div class="validationErr"><?php echo $passwordErr?></div>
    </div>

    <div class="form-group form-group-lg">
        <label class="col-md-2 control-label" for="passwordCheck">Password Again:</label>
            <div class="col-md-3">
                <input type="password" class="form-control" name="passwordCheck" value="">
                <br>
                <button type="submit" class="btn btn-success" name="submit">submit</button>
                <input type="submit" value="Clear" name="clear" class="btn btn-success">
            </div>
        <div class="validationErr"><?php echo $passwordCheckErr?></div>
    </div>


</div>
</form>

<?php include ('./Common/Footer.php'); ?>