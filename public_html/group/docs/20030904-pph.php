<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003 The PEAR Group                                    |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/
response_header("The PEAR Group: Handling Package Proposals");
?>

<h1>PEAR Group - Administrative Documents</h1>

<h2>Handling Package Proposals</h2>

<p>Published: 04th September 2003</p>

<p>Proposed packages cannot be released without going through the following
process. Packages found to be in violation will be summarily removed. We
plan to incorporate the actual voting into a web interface to simplify the
rights management and counting aspects.</p>

<ol>
  <li><h3>Initial Proposal</h3>
  
  <p>In order to make a proposal anyone can mail the pear development
  list with the details of his proposal. It is expected that the 
  proposer reviews other similar packages in PEAR which cover similar 
  functionality and state where the differences lie and provides links 
  to the source code of the package as a .phps file(s) along with 
  examples of how to use that code to enable code review (note: in 
  rare cases exceptions can be made to the source code regulation however 
  especially for newcomers it will be very unlikely that a proposal is
  accepted without some initial code).</p>

  </li>
  <li><h3>Calling for a vote</h3>

  <p>After some discussion on the list the proposer can &quot;call for 
  a vote&quot; by sending a mail to both the PEAR developer list and 
  the PEAR group list. In this period the actual voting will take place. 
  The proposer should only do this once he/she feels confident that 
  people have agreed on a package name. After one week following that 
  request the vote is closed and the results are tallied up.</p>

  </li>
  <li><h3>Votes</h3>
  
  <p>Only the votes of active members of the PEAR community (must have 
  a pear web account, however the proposer himself is not counted) are 
  counted, however anyone may vote. Votes require that a final choice 
  of package name is specified.</p>

  <p>The votes are added up, which means that one -1 offsets a +1. 
  However -1 vote should always be considered to be serious and may 
  lead to decisions being made on a case by case basis by the PEAR 
  Group who reserves a veto (it is intended that in the future the 
  PEAR QA team will assist the PEAR Group in such situations). 
  Therefore a -1 vote *must* contain a comment explaining that 
  decision, it is desirable that votes in favour (+1) should also be 
  accompanied with an explanation when appropriate.</p>

  <p>It is also expected that every voter make the level of review 
  clear in the vote and that every voter at least skim all provided 
  information from the proposer (especially if code/examples are 
  provided).</p>

  </li>
  <li><h3>Conditional votes</h3>

  <p>Conditional votes are not to be counted as final votes, instead 
  they serve to show what conditions have to be met. It's up to the 
  voter to decide in the end of the conditions have been met and make 
  a final vote. It should be made very clear that it is a conditional 
  vote to avoid confusion.</p>

  </li>
  <li><h3>Voting Results</h3>

  <p>A vote is accepted if the total of all votes exceeds +5.</p>

  <p>In case the proposal is not accepted the package can be further 
  discussed on the list and the proposer may attempt to make another 
  &quot;call for vote&quot; (it is expected that this is only done for 
  sensible reasons).</p>

  <p>In case the proposal is accepted the package may be registered 
  by the proposer and a mail should be send to the PEAR Group (it is 
  intended that in future the PEAR QA team will handle this) who will 
  then set the package as approved (however the PEAR Group reserves 
  final judgement).</p>

  <p>In order for the PEAR Group to approve a package the person who 
  made the proposal should include in the email to the PEAR Group 
  list all relevant information (package name, license, category, 
  type summary, full description, project homepage, links to all 
  votes in the mailinglist archive etc.)</p>

  </li>
</ol>

<?php
echo make_link("/group/", "Back");

response_footer();
?>

