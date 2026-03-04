function confirmLogout() {
    swal({
        title: "Are you sure?",
        text: "You will be logged out of the system",
        icon: "warning",
        buttons: {
            cancel: {
                text: "Cancel",
                value: null,
                visible: true,
                className: "",
                closeModal: true,
            },
            confirm: {
                text: "Yes, Logout",
                value: true,
                visible: true,
                className: "btn-danger",
                closeModal: true
            }
        },
        dangerMode: true
    })
    .then((willLogout) => {
        if (willLogout) {
            window.location.href = 'logout.php';
        }
    });
}