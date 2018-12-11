=== Quiz Tool Lite ===
Contributors: alexfurr, lcw102, simon.ward
Tags: academic, assessment, formative, quiz, questions
Requires at least: 3.0.1
Tested up to: 4.8.2
Stable tag: 3.0

A light weight quiz tool aimed at academics wanting to create interactive learning content. Insert a single question or deploy an entire quiz.

== Description ==

This plugin allows you to display an individual question on a page, along with feedback for formative assessment using no form or page submission. 
It also handles summative assessment if you wish to deploy an entire quiz.


**Question Types**

- Single response / True False(radio)
- Multiple response (checkbox)
- Free Text Entry (text box)
- Fill in the blanks (multiple text boxes)
- Reflective question types (click to reveal)

<br>
**Features**

- Embed a single question on a post or page for formative assessment (no form posting)
- Create multiple quizzes from your question 'pots' and deploy on a page for recording student scores
- Adds an 'Insert Question' link to the editor toolbar
- Uses shortcodes
- Export / import questions from one WP site to another
- Can display participants responses to previously submitted reflective questions on different pages
- Display scoreboards of quizzes on a page using a shortcode

<br>
**Quiz Options**

- Date window for availability
- Restrict access to logged in users
- Limit max attempts for logged in users
- Add a time limit that auto submits the page after x minutes and x seconds
- Limit time between attempts e.g. once a day
- Optional redirect to abother website once the quiz is completed
- Email participants an admin once a quiz has been completed
- Security measures so participants cannot use the browser back button to correct and resubmit
- Grade Boundaries to present different feedback to users depending on their grade.





== Installation ==

1.  Extract the zip file and copy contents in the wp-content/plugins/ directory of your WordPress installation
2.  Activate the Plugin from Plugins page.

== Frequently Asked Questions ==

**Does this do summative assessment?**

Yes the quiz can record answers if a quiz is deployed. If you deploy an individual question feedback is given but responses are not saved. This can be very useful for formative assessment.

**How do I use this tool?**

To display a question.

1. Create a question pot (questions must be stored in a question pot)

2. Create your questions within the pot.

3. Each question has its own shortcode which can be pasted into a post or page OR you can use the 'in editor' button to select a specific question if you are unsure how to use shortcodes.

**To deploy a quiz.**

1.  Create a new quiz and choose to display 'x' questions from pot 1 and (for example) 'x' questions at random from pot 2.

2.  Copy the quiz shortcode onto a page or use the 'in editor' button to add the quiz

The quiz saves the HIGHEST score and quizzes can be taken as often as possible, or you can limit attempts if you wish. It will only save the score if a user is logged in.

A results screen shows the highest score achieved by each registered user.

**Can anonymous users take a quiz**

Yes, but results won't be stored in the database

== Screenshots ==

1. A typical single choice question
2. A reflective question type (click to reveal)
3. The question pots page
4. Editing response options
5. Creating a quiz from question pots
6. Quiz Boundary example

== Changelog ==

= 2.3.12 =
* Bug fix for WPDB 4.8.2 upgrade

= 2.3.11 =
* Fixed lower case bug for blank text feedback


= 2.3.10 =
* Removed fnial legacy mysql references not compaitble with PHP 7

= 2.3.9 =
* Bug fix for text blank options with apostrophes on a quiz

= 2.3.8 =
* Bug fix for allowing showing feedback correctly on individual responses

= 2.3.7 =
* New option for changing Email notificatinos addresses and from names
* Bug fix for allowing test with apostrophes to be shown as correct


= 2.3.6 =* Critical Bug Fix
* Bug fix for showing time limit in quiz edit page
* Bug fix for wrong char sets resulting in no users being shown on results page

= 2.3.5 =* Critical Bug Fix= 2.3.4 =
* Removed all error messages if in debug mode
* Bug fix for allowing shortcodes in grade boundaries


= 2.3.3 =
* Bug fix for option ordering when manually assigning question IDs to quiz
* Bug fix for allowing shortcodes in grade boundaries

= 2.3.2 =
* Bug fix for HREF tags rendering in Boundary Feedback


= 2.3.1 =
* Bug fix for fill in the blanks if additional 'blanks' were added to the question text
* Fill in the blanks single question now does not show feedback box unless there is actual feedback text


= 2.3 =
* Bug fix for rendering greek and other non A-Z characters.
* New question type 'Fill in the blanks'


= 2.2.3 =
* Bug fix for allowing "0" (zero) as a response option
* New shortcode option for showing a students all their scores for a quiz
* Fix so that a radio button answer for a student can be shown to them (in addition to reflective submissions)


= 2.2.2 =
* Essential Bug fix for QUestion and Quiz Edit on the backend


= 2.2.1 =
* Improved Shortcode handling for stricter servers. Thanks to sakra00 for pointing out the original limitations.


= 2.2 =
* Added Grade Boundaries functionality so you can display feedback to users based on their score
* Can now customise the images shown (tick and cross) shown when answers are correct or not
* View detailed feedback on each users submitted answers for each quiz. Retains full version of their submitted answers for each attempt
* New shortcode parameters for tick cross options and feedback messages
* BUG FIX - single question embed now works with bootstrap based themes. Thanks to Gregor (humanbodylab) and brenthays for the help with this


= 2.1.2 =
* Fix to remove redundant date picker code that broke some jquery functions

= 2.1.1 =
* Fix to remove debug text for buttons


= 2.1 =
* Added new shortcodes to view leaderboards and dispay the score of a quiz to the current logged in user
* Added shortcode attribute to single question  so you can change the text of the submit button (button="My Text")
* Added the ability to make the plugin available to other roles (Editor / Author / Contributor)
* Integrated feedback messages and main buttons with standard WP styles for consitency
* Quiz list and resukts page has been combined for a more streamlined look
* Quiz list now tells you how many people have taken a quiz
* Major under the hood cleanup and all methods now in appropriate classes

= 2.0 =
* Added the time limit option to quizzes
* Added the free text option question type. YOu can now define possible answers typed into a box
* Improved interface using jquery UI tabs
* Added the ability to create a quiz from a defined set of question IDs as well as question pots

= 1.5 =
* Added the option to email admins once a quiz has been completed
* Added the option to redirect to a new page after the quiz has been completed
* Fixed table formatting issue for certain question types

= 1.4.9 =
* Fixed manual response option ordering bug
* Fixed undefined vars errors appearing if debug mode was switched on for the front end
* Added image examples for question types in the select question type page
* Fixed problems for non logged in users taking quizzes from front end
* Much improved colour picker for feedback style

= 1.4.8 =
* Added div id="quizResults" to the results so this can be formatted as required using CSS
* Question response options can now be ordered or disaplyed at random
* Images now tranparent PNGs to work nicely with the new 1.8 grey background.
* Please deactivate and then reactivate plugin to make drag / drop ordering work correctly!

= 1.4.7 =
* Enqueue debug now fixed, CSS changed so H1 tags no longer global. Many other debug messages now fixed.

= 1.4.6 =
* Bug fix to stop adding double BR tags

= 1.4.5 =
* Bug fix when creating a quiz with only one question

= 1.4.4 =
* Added options for copying questions to different pots

= 1.4.3 =
* Added the option to email users their score after test completion

= 1.4.2 =
* Now displays time until next possible quiz attempt e.g. 5 hours, 32 minutes

= 1.4.1 =
* Bug fix that upped the attempt count incorrectly under certain conditions

= 1.3 =
* Added loads more quiz options such as max attempts, time window, time between attmempts etc
* Added jquery datepicker from WP core and imported smooothness theme from google CDN

= 1.2 =
* Fixed minor bugs and imported jquery from google CDN

= 1.1.1 =
* Improved UI
* Added screen shots

= 1.1 =
* First release