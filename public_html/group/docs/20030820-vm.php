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
response_header("The PEAR Group: Handling Votings and Membership");
?>

<h1>PEAR Group - Administrative Documents</h1>

<h2>Handling Votings and Membership</h2>

<p>Published: 20th August 2003</p>

<ol>
  <li><h3>Voting</h3>

  <p>When a vote is called, each member can vote &quot;yes&quot;,
  &quot;no&quot; or &quot;abstain&quot;. In the case of a tie, the
  issue is considered unresolved until a compromise can be reached
  and a positive re-vote is achieved.  If more than half of the voting
  members choose to abstain, the vote is also considered unresolved.</p>

  <p>Outside of the Group, individual voting shall be anonymous.
  Published results would appear as:</p>

  <p><blockquote>4 yes, 1 no, 1 abstention</blockquote></p>

  <p>Examples (for a 7 member group):
  <pre>
    7 yes, 0 no, 0 abstain      Pass
    2 yes, 4 no, 1 abstain      Fail
    3 yes, 3 no, 1 abstain      Unresolved (Fail)
    2 yes, 1 no, 4 abstain      Unresolved (Fail)
  </pre>
  Any member can call a vote, but it must be seconded by one other
  member before the vote can begin.  The voting period will begin the
  following midnight (UTC).  Each voting period lasts four days. 
  Votes that haven&apos;t been cast by the end of the voting period default
  to &quot;abstain&quot;.</p>

  </li>
  <li><h3>Membership</h3>

  <p>Membership is essentially for life.  A member can resign whenever
  they like or can be voted out of the Group by a majority vote of the
  other members.</p>

  <p>Proposing a new member works the same way.  The maximum size of
  the Group shall be limited to nine members to keep things
  manageable.</p>
  </li>
</ol>

<?php
echo make_link("/group/", "Back");

response_footer();
?>

