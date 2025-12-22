<?php
include '../connectMySql.php';

echo "Updating database schema for enhanced automatic issue detection...\n";

try {
    // Check if columns already exist before adding them
    $checkColumns = "SHOW COLUMNS FROM ticket";
    $result = $conn->query($checkColumns);
    $existingColumns = [];
    
    while ($row = $result->fetch_assoc()) {
        $existingColumns[] = $row['Field'];
    }
    
    echo "Existing columns: " . implode(', ', $existingColumns) . "\n";
    
    // Add location column if it doesn't exist
    if (!in_array('location', $existingColumns)) {
        $sql = "ALTER TABLE `ticket` ADD COLUMN `location` VARCHAR(255) DEFAULT NULL AFTER `description`";
        if ($conn->query($sql)) {
            echo "✓ Added location column to ticket table\n";
        } else {
            echo "✗ Error adding location column: " . $conn->error . "\n";
        }
    } else {
        echo "✓ Location column already exists\n";
    }
    
    // Modify issue_type to allow more types
    $sql = "ALTER TABLE `ticket` MODIFY COLUMN `issue_type` VARCHAR(255) NOT NULL";
    if ($conn->query($sql)) {
        echo "✓ Modified issue_type column to allow more issue types\n";
    } else {
        echo "✗ Error modifying issue_type column: " . $conn->error . "\n";
    }
    
    // Add auto_detected column if it doesn't exist
    if (!in_array('auto_detected', $existingColumns)) {
        $sql = "ALTER TABLE `ticket` ADD COLUMN `auto_detected` BOOLEAN DEFAULT FALSE AFTER `description`";
        if ($conn->query($sql)) {
            echo "✓ Added auto_detected column to ticket table\n";
        } else {
            echo "✗ Error adding auto_detected column: " . $conn->error . "\n";
        }
    } else {
        echo "✓ Auto_detected column already exists\n";
    }
    
    // Add severity column if it doesn't exist
    if (!in_array('severity', $existingColumns)) {
        $sql = "ALTER TABLE `ticket` ADD COLUMN `severity` ENUM('Low','Medium','High','Critical') DEFAULT 'Medium' AFTER `issue_type`";
        if ($conn->query($sql)) {
            echo "✓ Added severity column to ticket table\n";
        } else {
            echo "✗ Error adding severity column: " . $conn->error . "\n";
        }
    } else {
        echo "✓ Severity column already exists\n";
    }
    
    // Add category column if it doesn't exist
    if (!in_array('category', $existingColumns)) {
        $sql = "ALTER TABLE `ticket` ADD COLUMN `category` VARCHAR(255) DEFAULT 'General' AFTER `severity`";
        if ($conn->query($sql)) {
            echo "✓ Added category column to ticket table\n";
        } else {
            echo "✗ Error adding category column: " . $conn->error . "\n";
        }
    } else {
        echo "✓ Category column already exists\n";
    }
    
    // Update status column to accept more values
    $sql = "ALTER TABLE `ticket` MODIFY COLUMN `status` ENUM('Pending','In Progress','Resolved','PENDING','UNRESOLVED','RESOLVED') DEFAULT 'Pending'";
    if ($conn->query($sql)) {
        echo "✓ Updated status column to accept more values\n";
    } else {
        echo "✗ Error updating status column: " . $conn->error . "\n";
    }
    
    echo "\nDatabase schema update completed!\n";
    echo "The automatic issue detection system is now ready to use.\n";
    
} catch (Exception $e) {
    echo "✗ Error updating database schema: " . $e->getMessage() . "\n";
}

$conn->close();
?>