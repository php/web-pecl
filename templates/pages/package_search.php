<?php $this->extend('layout.php', ['title' => 'Package search']) ?>

<?php $this->start('head') ?>
    <link rel="stylesheet" href="/js/calendar/dynCalendar.css">
<?php $this->end('head') ?>

<?php $this->start('content') ?>

<h1>Package search</h1>

<script src="/js/calendar/browserSniffer.js"></script>
<script src="/js/calendar/dynCalendar.js"></script>

<script>
    date_updated_released_on     = false;
    date_updated_released_before = false;
    date_updated_released_since  = false;

    released_on_disabled     = false;
    released_before_disabled = false;
    released_since_disabled  = false;

    /**
    * Resets the above variables to false when form is cleared
    */
    function form_reset()
    {
        searchForm = document.forms['search_form'];

        if (<?= (int) isset($numrows) ?>) {
            location.href = 'package-search.php';
        } else {
            date_updated_released_on     = false;
            date_updated_released_before = false;
            date_updated_released_since  = false;

            // Re-enable date dropdowns
            searchForm.released_before_year.disabled  = false;
            searchForm.released_before_month.disabled = false;
            searchForm.released_before_day.disabled   = false;

            searchForm.released_since_year.disabled  = false;
            searchForm.released_since_month.disabled = false;
            searchForm.released_since_day.disabled   = false;

            searchForm.released_on_year.disabled  = false;
            searchForm.released_on_month.disabled = false;
            searchForm.released_on_day.disabled   = false;

            released_on_disabled     = false;
            released_before_disabled = false;
            released_since_disabled  = false;

            // Re-enable search button
            searchForm.submitButton.disabled = false;
            return true;
        }
    }

    /**
    * When changed, the date fields in the forms are updated by this
    */
    function update_date(prefix, input)
    {
        searchForm = document.forms['search_form'];
        if (eval('date_updated_' + prefix)) return true;

        yearElement  = searchForm.elements[prefix + '_year'];
        monthElement = searchForm.elements[prefix + '_month'];
        dayElement   = searchForm.elements[prefix + '_day'];
        today = new Date();

        switch (input) {
            case 'year':
                if (monthElement.value != '' || dayElement.value != '') return true;
                monthElement.value = today.getMonth() + 1;
                dayElement.value = today.getDate();
                break;

            case 'month':
                if (yearElement.value != '' || dayElement.value != '') return true;
                yearElement.value = today.getFullYear();
                dayElement.value  = today.getDate();
                break;

            case 'day':
                if (yearElement.value != '' || monthElement.value != '') return true;
                yearElement.value  = today.getFullYear();
                monthElement.value = today.getMonth() + 1;
                break;
        }

        disableDateOptions(prefix);

        eval('date_updated_' + prefix + ' = true');
        return true;
    }

    /**
    * This function sets the date dropdowns to their
    * search values.
    */
    function setReleaseDropdowns()
    {
        if (<?= (int) $setReleasedOn ?>) {
            setDateFromCalendar_released_on('<?= isset($_GET['released_on_day']) ? (int) $_GET['released_on_day'] : '' ?>', '<?= isset($_GET['released_on_month']) ? (int) $_GET['released_on_month'] : '' ?>', '<?= isset($_GET['released_on_year']) ? (int) $_GET['released_on_year'] : '' ?>');
        } else {
            if (<?= (int) $setReleasedBefore ?>) {
                setDateFromCalendar_released_before('<?= isset($_GET['released_before_day']) ? (int) $_GET['released_before_day'] : '' ?>', '<?= isset($_GET['released_before_month']) ? (int) $_GET['released_before_month'] : '' ?>', '<?= isset($_GET['released_before_year']) ? (int) $_GET['released_before_year'] : ''?>');
            }

            if (<?= (int) $setReleasedSince ?>) {
                setDateFromCalendar_released_since('<?= isset($_GET['released_since_day']) ? (int) $_GET['released_since_day'] : '' ?>', '<?= isset($_GET['released_since_month']) ? (int) $_GET['released_since_month'] : '' ?>', '<?= isset($_GET['released_since_year']) ? (int) $_GET['released_since_year'] : '' ?>');
            }
        }
    }

    /**
    * Function to disable date dropdowns when the
    * others are selected.
    */
    function disableDateOptions(prefix)
    {
        // Disable appropriate option based on what just changed.
        searchForm = document.forms['search_form'];
        switch (prefix) {
            case 'released_on':
                searchForm.released_before_year.disabled  = true;
                searchForm.released_before_month.disabled = true;
                searchForm.released_before_day.disabled   = true;
                released_before_disabled = true;

                searchForm.released_since_year.disabled  = true;
                searchForm.released_since_month.disabled = true;
                searchForm.released_since_day.disabled   = true;
                released_since_disabled = true;
                break;

            case 'released_before':
            case 'released_since':
                searchForm.released_on_year.disabled  = true;
                searchForm.released_on_month.disabled = true;
                searchForm.released_on_day.disabled   = true;
                released_on_disabled = true;
                break;
        }
    }

    /**
    * Callback functions for the calendar
    */
    function setDateFromCalendar_released_on(date, month, year)
    {
        date_updated_released_on = true;
        return setDateFromCalendar('released_on', date, month, year);
    }

    function setDateFromCalendar_released_before(date, month, year)
    {
        date_updated_released_before = true;
        return setDateFromCalendar('released_before', date, month, year);
    }

    function setDateFromCalendar_released_since(date, month, year)
    {
        date_updated_released_since = true;
        return setDateFromCalendar('released_since', date, month, year);
    }

    function setDateFromCalendar(prefix, date, month, year)
    {
        searchForm = document.forms['search_form'];

        if (eval(prefix + '_disabled') == true) {
            return;
        } else {
            disableDateOptions(prefix);
        }
        yearElement  = searchForm.elements[prefix + '_year'].value = (year == '0' ? '' : year);
        monthElement = searchForm.elements[prefix + '_month'].value = (month == '0' ? '' : month);
        dayElement   = searchForm.elements[prefix + '_day'].value = (date == '0' ? '' : date);
    }

    function validate_form()
    {
        searchForm = document.forms['search_form'];

        onYearElement  = searchForm.elements['released_on_year'];
        onMonthElement = searchForm.elements['released_on_month'];
        onDayElement   = searchForm.elements['released_on_day'];

        beforeYearElement  = searchForm.elements['released_before_year'];
        beforeMonthElement = searchForm.elements['released_before_month'];
        beforeDayElement   = searchForm.elements['released_before_day'];

        sinceYearElement  = searchForm.elements['released_since_year'];
        sinceMonthElement = searchForm.elements['released_since_month'];
        sinceDayElement   = searchForm.elements['released_since_day'];

        released_on_changed     = (onYearElement.value     != '' || onMonthElement.value     != '' || onDayElement.value     != '');
        released_before_changed = (beforeYearElement.value != '' || beforeMonthElement.value != '' || beforeDayElement.value != '');
        released_since_changed  = (sinceYearElement.value  != '' || sinceMonthElement.value  != '' || sinceDayElement.value  != '');

        if (released_on_changed && (released_since_changed || released_before_changed)) {
            alert('Cannot combine Released On and Released Before or Since!');
            return false;
        }

        document.forms['search_form'].submitButton.value    = 'Sending request...';
        document.forms['search_form'].submitButton.disabled = true;
    }
</script>

<form action="<?= $this->e($_SERVER['PHP_SELF']) ?>" method="get" name="search_form" onsubmit="validate_form()">
    <table class="form-holder" cellspacing="1">
        <caption class="form-caption">Search Options</caption>
        <tr>
            <th class="form-label_left">Sear<span class="accesskey">c</span>h for:</th>
            <td class="form-input">
                <input type="text" name="pkg_name" size="0" value="<?= (!empty($_GET['pkg_name'])) ? $this->e($_GET['pkg_name']) : ''; ?>" accesskey="c">
            </td>
        </tr>

        <tr>
            <th class="form-label_left">Maintainer:</th>
                <td class="form-input">
                    <input name="pkg_maintainer" type="text" value="<?= $this->e(!empty($_GET['pkg_maintainer']) ? $_GET['pkg_maintainer'] : '') ?>">
                    <select onchange="document.forms['search_form'].pkg_maintainer.value = this.options[this.selectedIndex].value; this.selectedIndex = 0">
                        <option value="">Select user...</option>
                        <?php foreach($users as $u): ?>
                            <option value="<?= $this->e($u['handle']) ?>"><?= $this->e($u['name']) ?></option>
                        <?php endforeach ?>
                    </select>
                </td>
            </tr>

        <tr>
            <th class="form-label_left">Category:</th>
            <td class="form-input">
                <select name="pkg_category">
                    <option value=""></option>
                    <?php foreach($categories as $category): ?>
                        <option value="<?= $this->e($category['id']) ?>" <?= (!isset($category['selected']) ? '' : $category['selected'] ) ?>><?= $this->e($category['name']) ?></option>
                    <?php endforeach ?>
                </select>
            </td>
        </tr>

        <tr><td class="form-input" colspan="2">&nbsp;</td></tr>

        <tr>
            <th class="form-label_left" colspan="2">With a release...</th>
        </tr>

        <tr>
            <th class="form-label_left">On:</th>
            <td class="form-input">
                <input type="text" name="released_on_year" value="" size="5" onkeyup="update_date('released_on', 'year')">
                <select name="released_on_month" onchange="update_date('released_on', 'month')">
                    <option value=""></option>
                    <?php foreach($months as $num => $text): ?>
                        <option value="<?= $this->e($num) ?>"><?= $this->e($text) ?></option>
                    <?php endforeach ?>
                </select>
                <select name="released_on_day" onchange="update_date('released_on', 'day')">
                    <option value=""></option>
                    <?php foreach(range(1, 31) as $value):?>
                        <option value="<?= $this->e($value) ?>"><?= $this->e($value) ?></option>
                    <?php endforeach?>
                </select>
                <script>
                    calendarReleasedOn = new dynCalendar('calendarReleasedOn', 'setDateFromCalendar_released_on', 'img/');
                </script>
            </td>
        </tr>

        <tr>
            <th class="form-label_left">Before:</th>
            <td class="form-input">
                <input type="text" name="released_before_year" value="" size="5" onkeyup="update_date('released_before', 'year')" />
                <select name="released_before_month" onchange="update_date('released_before', 'month')">
                    <option value=""></option>
                    <?php foreach($months as $num => $text): ?>
                        <option value="<?= $this->e($num) ?>"><?= $this->e($text) ?></option>
                    <?php endforeach ?>
                </select>

                <select name="released_before_day" onchange="update_date('released_before', 'day')">
                    <option value=""></option>
                    <?php foreach(range(1, 31) as $value): ?>
                        <option value="<?= $this->e($value) ?>"><?= $this->e($value) ?></option>
                    <?php endforeach ?>
                </select>

                <script>
                    calendarReleasedBefore = new dynCalendar('calendarReleasedBefore', 'setDateFromCalendar_released_before', 'img/');
                </script>
            </td>
        </tr>

        <tr>
            <th class="form-label_left">Since:</th>
            <td class="form-input">
            <input type="text" name="released_since_year" value="" size="5" onkeyup="update_date('released_since', 'year')" />
            <select name="released_since_month" onchange="update_date('released_since', 'month')">
                <option value=""></option>
                <?php foreach($months as $num => $text): ?>
                    <option value="<?= $this->e($num) ?>"><?= $this->e($text) ?></option>
                <?php endforeach ?>
            </select>
            <select name="released_since_day" onchange="update_date('released_since', 'day')">
                <option value=""></option>
                <?php foreach(range(1, 31) as $value): ?>
                    <option value="<?= $this->e($value) ?>"><?= $this->e($value) ?></option>
                <?php endforeach ?>
            </select>
            <script>
                calendarReleasedSince = new dynCalendar('calendarReleasedSince', 'setDateFromCalendar_released_since', 'img/');
            </script>
            </td>
        </tr>

        <tr>
            <th class="form-label_left">&nbsp;</th>
            <td class="form-input">
                <input type="submit" name="submitButton" value="Search">
                <input type="reset" value="Clear" onclick="return form_reset()">
            </td>
        </tr>
    </table>
</form>

<script>
    // Call function to set dropdowns to their search values.
    setReleaseDropdowns();
</script>

<?php if($numrows): ?>
<br><br>
<table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
    <tr>
        <td bgcolor="#000000">
            <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                <tr style="background-color: #CCCCCC;">
                    <th><?= $titleHtml ?></th>
                </tr>
                <tr bgcolor="#ffffff">
                    <td>

                        <table border="0" cellpadding="2" cellspacing="2">
                            <?php foreach($searchResults as $row): ?>
                                <tr>
                                    <td>
                                        <a href="/package/<?= $this->e($row['raw_name']) ?>"><?= $row['name'] ?></a>
                                    </td>
                                    <td><?= $row['summary'] ?></td>
                                </tr>
                            <?php endforeach ?>
                        </table>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
<?php elseif (0 === $numrows): ?>
    <p><b>No results found</b></p>
<?php endif?>

<?php $this->end('content') ?>
