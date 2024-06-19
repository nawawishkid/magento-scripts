<?php
use Magento\Framework\App\Bootstrap;
use Magento\User\Model\UserFactory;
use Magento\Framework\Encryption\EncryptorInterface;

require 'app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();

$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('adminhtml');

$userFactory = $obj->get(UserFactory::class);
$encryptor = $obj->get(EncryptorInterface::class);

function readCSV($csvFile){
    $file_handle = fopen($csvFile, 'r');
    while (!feof($file_handle) ) {
        $line_of_text[] = fgetcsv($file_handle, 1024);
    }
    fclose($file_handle);
    return $line_of_text;
}

// Path to the CSV file
$csvFile = '/tmp/admin_users.csv';

// Read CSV file
$adminUsersData = readCSV($csvFile);

// Skip the header row
array_shift($adminUsersData);

foreach ($adminUsersData as $data) {
    if (empty($data)) continue; // Skip empty rows
    try {
        $user = $userFactory->create();
        $user->loadByUsername($data[0]);

	if (!$user->getId()) {
		echo "Admin user {$data[0]} does not exist, creating...\n";
        $user->setData([
            'username' => $data[0],
            'firstname' => $data[1],
            'lastname' => $data[2],
            'email' => $data[3],
            'password' => $data[4],
            'role_id' => $data[5],
            'is_active' => $data[6],
            'interface_locale' => $data[7],
        ]);
        $user->save();
	$now = date('Y-m-d-H:i:s');
        echo "Admin user {$data[0]} created successfully at $now (UTC).\n";
	} else {
		echo "Admin user {$data[0]} already exist, updating...\n";
		$user->setFirstName($data[1]);
		$user->setLastName($data[2]);
		$user->setEmail($data[3]);
		$user->setPassword($data[4]);
		$user->setRoleId($data[5]);
		$user->setIsActive($data[6]);
		$user->setInterfaceLocale($data[7]);
		$user->save($user);
		$now = date('Y-m-d-H:i:s');
		echo "Admin user {$data[0]} updated successfully at $now (UTC).\n";
	}
    } catch (\Exception $e) {
        echo "Error creating admin user {$data[0]}: " . $e->getMessage() . "\n";
    }
}

