<a name="menu"></a>
<h2>Help</h2>
<ul>
<li><a href="#overview">Overview</a></li>
<li><a href="#pots">Getting stared : Creating your question pots</a></li>
<li><a href="#questions">Creating Questions</a></li>
<li><a href="#feedback">Question feedback</a></li>
<li><a href="#insertQuestion">Adding questions to a page</a></li>
<li><a href="#quiz">Creating Quizzes</a></li>
<li><a href="#insertQuiz">Adding a quiz to a page</a></li>
<li><a href="#shortcodes">Shortcodes</a></li>
<li><a href="#showResponse">Showing user responses</a></li>
<li><a href="#results">Viewing Results</a></li>
<li><a href="#help">More Help</a></li>
</ul>
<a name="overview"></a><hr />

<style>

td
{
	border-bottom:1px solid #ccc;
	font-size:12px;
}
</style>

<h2>Overview</h2>
This plugin will allow you to create quiz questions and deploy them as a single question on a page, or as an entire quiz.<br/>
Questions are stored in 'pots' which allow you to create custom quizzes from different questions in each pot. You can think of a question pot as a bucket in which you store similar questions<br/>
e.g. you could have 3 question pots called 'Easy', 'Medium' and 'Hard'. You can then create a quiz with 5 questions from the 'Easy' pot, 5 questions from the 'Medium' pot and 5 questions from the 'Hard' pot.
<br/>Quiz questions are assigned randomly so in the above example if you had 10 questions in each pot you would be displaying 15 random questions, 5 from each difficulty to each individual taking the quiz (see below)
<br/><br />

<?php
//echo home_url();
$homeURL =  network_home_url();

if($homeURL =="")
{
	$homeURL = home_url();
}

$imgSrc =  $homeURL.'/wp-content/plugins/quiz-tool-lite/images/help/pot-example.jpg';
//echo $imgSrc;

echo '<div style="text-align:center"><img src="'.$imgSrc.'" />';
echo '<br/><span class="greyText">An example showing a quiz made up of 2 questions at random from 3 pots.</span>';
echo '</div>';

?>
<a name="pots"></a><br />
<br /><span class="smallText greyLink"><a href="#menu">Back to menu</a></span>


<hr/>
<h2>Getting started : Creating your question pots</h2>
First off you need to create a question pot to add your questions to. Click 'Quiz Questions' from the menu and then 'Create a new question pot'<br/>
You can call your question pot anything you want e.g. 'Geography questions'. People taking the quiz will never see the name of your question pots.<br/>
You can also change your question pot names at any point<br/>
Once you've created a question pot click the 'Add / edit questions' link to start adding questions
<a name="questions"></a><br />
<br /><span class="smallText greyLink"><a href="#menu">Back to menu</a></span>

<hr/>
<h2>Creating questions</h2>
Once you've created a question pot and have clicked 'Add / edit questions' you should see a new link 'Add a new question'. Click this and you're taken through to a page that
asks you to pick a question type. There are currently 4 questions types to choose from.<br /><br />

<b>Single Answer (Radio Buttons)</b><br/>
This question type allows participants to select ONE and only one answer. Use this for creating True / False questions, or when only one answer is correct e.g. what is the capital of France.

<br/><br />
<b>Multiple Answer (check boxes)</b><br/>
This question type allows participants to select more than one answer e.g. which of the following are true / select all that apply.
<br/><br />

<b>Free Text</b><br/>
This question type allows participants to add text into a box. You can identify as many correct answers as you wish. These are not case sensitive. i.e. "Bones" would be accepted as well as "bones".
<br/><br />

<b>Reflection</b><br />
You can use this question type when you want to give your participants a statement to think about, and then click a button to reveal a model answer.
This can be used as a 'click to reveal' question type simply present information to people (and does not give any means to enter a response, but there is also an option for students to enter a response as well.<br /><br />

<a name="feedback"></a><br />
<br /><span class="smallText greyLink"><a href="#menu">Back to menu</a></span>
<hr/>
<h2>Question feedback</h2>
Each question has several options for giving feedback. They have an overall correct and incorrect feedback box, but each response option (if applicable) can also have feedback for correct and incorrect feedback.<br />
Feedback is not required and can be left blank. Feedback is given automatically when adding a single question - you do not need to 'enable' it in anyway.

<a name="insertQuestion"></a><br />
<br /><span class="smallText greyLink"><a href="#menu">Back to menu</a></span>
<hr/>
<h2>Adding questions to a page</h2>
Each question has it's own uniqye shortcode which will looking something like this:
<div class="ek-code">[ek-question id=123]</div>

Copy this shortcode ontp any page and it will render the individual question.
<br/>
<a href="#shortcodes">See the list of shortcodes for a complete list</a>


<a name="quiz"></a><br />
<br /><span class="smallText greyLink"><a href="#menu">Back to menu</a></span>
<hr/>
<h2>Creating Quizzes</h2>
A quiz is created by pulling in X number of questions from X number of question pots. In its most simple formar, if you had one question pot with ten questions you could create a quiz that pulled in 10 questions from that pot.<br />
That would create a quiz with all 10 questions being displayed at random. However, you can combine multiple questions from seperate question pots to make each participant see a slightly different version of the quiz.
<br />
Results from quizzes for logged in users are stored and can be viewed in the 'Results' page. The highest score is saved for each participant.
<a name="insertQuiz"></a><br />
<br /><span class="smallText greyLink"><a href="#menu">Back to menu</a></span>
<hr/>
<h2>Adding a quiz to a page</h2>
Each quiz has it's own shortcode which is shown on the quiz list page. for example:
<div class="ek-code">[ek-quiz id=2]</div>
would show the quiz ID 2.<br/>
Simply copy this shortcode onto and page it will display the quiz.




<a name="shortcodes"></a><br />
<br /><span class="smallText greyLink"><a href="#menu">Back to menu</a></span>

<hr/>

<h2>Shortcodes</h2>
To add a question or a quiz to a page you need to use 'shortcodes'. A shortcode is a simple bit of text that is added between to square brackets e.g. [My Shortcode].<br />
On the whole you will not need to know anything else about shortcodes to add questions to a page. Simply use the 'Insert question Wizard' (see above) and the shortcode will be generated and added to the page for you.i
<br />
Example shortcodes are shown below<br />
<br />



<h4>Standard Shortcode Examples</h4>
<table style="line-height:50px; text-align:left; border-bottom:1px solid #ccc">
<tr>
<th width="350px">Shortcode</th>
<th>Description</th>
</tr>
<tr>
<td>
<span class="codeExample">[ek-question id=25]</span>
</td>
<td>
Insert question ID 25.
</td>
</tr>
<tr>
<td>
<span class="codeExample">[ek-question id=25 savedata=true]</span>
</td>
<td>
Insert question ID 25 and save the response to the database
</td>
</tr>
<tr>
<td>
<span class="codeExample">[ek-question id=25 button="Click Here"]</span>
</td>
<td>
Insert question ID 25 and change the default "Check Answer" button text to "Click Here"
</td>
</tr>

<tr>
<td>
<span class="codeExample">[ek-question id=25 correctfeedback="Well done!"]</span>
</td>
<td>
Insert question ID 25 and change the default correct feedback
</td>
</tr>

<tr>
<td><span class="codeExample">[ek-question-response id=25]</span></td>
<td>Shows the response given to question ID 25 by the current logged in user</td>
</tr>
<tr>
<td><span class="codeExample">[ek-quiz id=2]</span></td>
<td>Insert quiz ID 2 into the page</td>
</tr>
<tr>
<tr>
<td><span class="codeExample">[ek-quiz-leaderboard id=2]</span></td>
<td>Draw the leaderboard showing all user scores for quiz ID 2</td>
</tr>
<tr>
<td><span class="codeExample">[ek-quiz-leaderboard id=2 anonymous=true]</span></td>
<td>Draw the leaderboard showing all user scores for quiz ID 2 but hiding their names</td>
</tr>

</table>

<h4>Shortcodes available for the single question [ek-question]</h4>
<table style="line-height:50px; text-align:left; border-bottom:1px solid #ccc">

<tr>
<td><span class="codeExample">savedata=true</span></td>
<td>Saves the response to the database so you can display to the user on another page</td>
</tr>

<tr>
<td><span class="codeExample">button="Click here"</span></td>
<td>Changes the default button text of the show answer button</td>
</tr>


</table>



<div id="iconsets" style="display:none">
<h3>Available Iconsets</h3>
<?php


$iconArray = array();
$iconArray = qtl_utils::getQTL_IconArray();


$correctIconDir = QTL_PLUGIN_URL.'/images/icons/correct/';
$incorrectIconDir = QTL_PLUGIN_URL.'/images/icons/incorrect/';
echo '<table>';
$i=1;
foreach($iconArray as $myIcon)
{
	$currentIconNo = substr($myIcon, 4, -4);
	$correctIconRef = $correctIconDir.'/'.$myIcon;
	$incorrectIconRef = $incorrectIconDir.'/cross'.$currentIconNo.'.png';
	if($i==1){echo '<tr>';}
	echo '<td align="center" style="padding:25px">';
	echo '<img src="'.$correctIconRef.'">';
	echo '<img src="'.$incorrectIconRef.'"><br/>';
	echo 'Iconset '.$currentIconNo;
	if($currentIconNo==1){echo ' (Default)';}
	echo '</td>';
	$i++;
	if($i>=5){$i=1; echo '</tr>';}
}
if($i<>1){echo '</tr>';}
echo '</table>';
?>


</div>


<a name="showResponse"></a><br />
<br /><span class="smallText greyLink"><a href="#menu">Back to menu</a></span>

<hr/>

<h2>Showing user responses</h2>
It is possible to present participants with a response they gave to a previous question. This is particularly useful for the 'Reflective' question types where
you wish to present their responses to them at a later stage in their learning journey.<br />
Do this you need to do the following:<br />
<br />

<b>1. Ensure data is being saved by manually modifying the shortcode</b><br />
A typical shortcode for a question is as follows:<br /><br />


<span class="codeExample">[ek-question id=25]</span><br /><br />
To make the question save the data simply add 'savedata=true' to the shoprtcode as shown below<br /><br />

<span class="codeExample">[ek-question id=25 savedata=true]</span><br /><br />

<b>2. Add the 'Show Response' shortcode</b><br />
To display the response submitted for question with ID of 25, add the following to your page or post<br />
<br />

<span class="codeExample">[QTL-Response id=3]</span><br /><br />


<i>Please note that currently this is only properly supported with reflective question types (text boxes), and only with formative questions i.e. single questions, not quizzes.</i>


<a name="results"></a><br />
<br /><span class="smallText greyLink"><a href="#menu">Back to menu</a></span>

<hr/>
<h2>Viewing Results</h2>
The results page will show you a list of quizzes you have created. Clicking the 'View Results' link will display all registered users on your site, along with their highest score achieved.<br />
Please note that currently quizzes can be taken as many times as they wish, and only the highest score will be recorded.


<a name="help"></a><br />
<br /><span class="smallText greyLink"><a href="#menu">Back to menu</a></span>

<hr/>
<h2>Need more help?</h2>
If you require more help please add your question to the <a href="http://wordpress.org/support/plugin/quiz-tool-lite
">support forum</a> where we will be in touch ASAP.
<br /><span class="smallText greyLink"><a href="#menu">Back to menu</a></span>
