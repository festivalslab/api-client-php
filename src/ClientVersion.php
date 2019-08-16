<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */

$commit = '$Format:%h$'; // will be replaced by export-subst
if ($commit[0] === '$') {
    return 'unknown-dev';
} else {
    return $commit;
}
