<?php 
    session_start();
    session_unset();  
    session_destroy();  

    // Clear localStorage with JavaScript right after page load
    echo "<script>
        localStorage.removeItem('lastPage'); 
        window.location.href = 'index.php';
    </script>";

    exit();
?>
