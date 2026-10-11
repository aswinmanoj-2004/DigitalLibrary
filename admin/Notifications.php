<?php
require('../config/autoload.php');
include("header.php");

$dao = new DataAccess();

$elements = array("receiver_type"=>"", "receiver_id"=>"0", "title"=>"", "message"=>"");
$form     = new FormAssist($elements, $_POST);
$labels   = array('receiver_type'=>"Send To", 'title'=>"Title", 'message'=>"Message");
$rules    = array(
    'receiver_type' => array('required'=>true),
    'title'         => array('required'=>true, 'minlength'=>3, 'maxlength'=>100),
    'message'       => array('required'=>true, 'minlength'=>5)
);
$validator = new FormValidator($rules, $labels);
$msg = "";

if (isset($_POST['btn_send'])) {
    if ($validator->validate($_POST)) {
        $data = array(
            'receiver_id'   => (int)$_POST['receiver_id'],
            'receiver_type' => $_POST['receiver_type'],
            'title'         => $_POST['title'],
            'message'       => $_POST['message'],
            'sent_date'     => date('Y-m-d H:i:s'),
            'status'        => 1
        );
        if ($dao->insert($data, 'notifications')) {
            echo "<script>alert('Notification sent successfully'); window.location.href='Notifications.php';</script>";
            exit();
        } else {
            $msg = "Failed to send notification.";
        }
    }
}
?>

<!-- Send Form -->
<div class="section" style="max-width:640px;">
    <h2>📤 Send Notification</h2>

    <?php if ($msg != ""): ?>
        <div class="alert alert-danger"><?= $msg ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <label>Send To</label>
        <?php
        $options = array("All Readers"=>"reader", "All Authors/Sellers"=>"author", "Everyone"=>"all");
        echo $form->dropDownList('receiver_type', array('class'=>'form-control'), $options);
        ?>
        <div style="color:#ef4444;font-size:12px;margin-top:-12px;margin-bottom:10px;"><?= $validator->error('receiver_type') ?></div>

        <label>Notification Title</label>
        <?= $form->textBox('title', array('class'=>'form-control')) ?>
        <div style="color:#ef4444;font-size:12px;margin-top:-12px;margin-bottom:10px;"><?= $validator->error('title') ?></div>

        <label>Message</label>
        <textarea name="message" class="form-control" rows="5"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        <div style="color:#ef4444;font-size:12px;margin-top:4px;margin-bottom:14px;"><?= $validator->error('message') ?></div>

        <button type="submit" name="btn_send" class="btn btn-primary">🔔 Send Notification</button>
    </form>
</div>

<!-- Sent Notifications List -->
<div class="section">
    <h2>📋 Sent Notifications</h2>
    <div class="table-wrap">
        <table>
            <tr>
                <th>Sl</th>
                <th>Title</th>
                <th>Message</th>
                <th>Sent To</th>
                <th>Date</th>
            </tr>
            <?php
            $config = array('srno'=>true, 'hiddenfields'=>array('notifid'));
            $fields = array('notifid','title','message','receiver_type','sent_date');
            echo $dao->selectAsTable($fields, 'notifications', 1, array(), array(), $config);
            ?>
        </table>
    </div>
</div>

<?php include("footer.php"); ?>
