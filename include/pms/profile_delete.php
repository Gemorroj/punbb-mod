<?php

// Delete users private messages
$db->query('DELETE FROM '.$db->prefix.'messages WHERE owner='.$id) || \error('Unable to delete users messages', __FILE__, __LINE__, $db->error());
