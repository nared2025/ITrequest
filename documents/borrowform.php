<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/borrowform.css">
    <title>Borrow-Form</title>
</head>

<body>
    <div class="form-container">
        <div class="form-header">
            <h1>IT BORROW / RETURN ITEM FORM</h1>
        </div>

        <form>
            <!-- Employee ID -->
            <div class="form-group">
                <div class="form-group-half">
                    <label for="employee-id">Employee ID</label>
                    <input type="text" id="employee-id" placeholder="A0000">
                </div>
                <!-- Building Number -->
                <div class="form-group-half">
                    <label for="building-number">Request no.</label>
                    <input type="text" id="building-number">
                </div>
            </div>

            <!-- Full Name -->
            <div class="form-group">
                <div class="form-group-half">
                    <label for="full-name">Full Name</label>
                    <input type="text" id="full-name">
                </div>
            </div>

            <!-- Department -->
            <div class="form-group">
                <div class="form-group-half">
                    <label for="department">Department</label>
                    <input type="text" id="department">
                </div>
            </div>

            <!-- Problem Category -->
            <div class="form-group">
                <label>Problem Category</label>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="issue-category" value="Mouse">Mouse</label>
                    <label><input type="checkbox" name="issue-category" value="Keyboard">Keyboard</label>
                    <label><input type="checkbox" name="issue-category" value="Monitor"> Monitor</label>
                    <label><input type="checkbox" name="issue-category" value="Notebook"> Notebook</label>
                    <label><input type="checkbox" name="issue-category" value="Printer"> Printer</label>
                    <label><input type="checkbox" name="issue-category" value="Others">Others</label>
                </div>
            </div>

            <div class="form-group">
                <div class="form-group-inline">
                    <label for="Quantity">Quantity</label>
                    <input type="text" id="department">
                </div>
                <div class="form-group-inline">
                    <label for="building-number">Duration (Days)</label>
                    <input type="text" id="building-number" placeholder=" 7 days">
                </div>
                <div class="form-group-inline">
                    <label for="building-number">Form</label>
                    <input type="date" id="building-number">
                </div>
                <div class="form-group-inline">
                    <label for="building-number">To</label>
                    <input type="date" id="building-number">
                </div>
            </div>

            <!-- Details Problem -->
            <div class="form-group">
                <label for="Purpose">Purpose</label>
                <textarea id="Details"></textarea>
            </div>

            <!-- Request by -->
            <div class="form-group">
                <div class="form-group-half">
                    <label for="request-by">Request by</label>
                    <input type="text" id="request-by">
                </div>
                <div class="form-group-half">
                    <label for="date">Date/Time</label>
                    <input type="date" id="date">
                </div>
            </div>
            <!-- Approved by -->
            <div class="form-group">
                <div class="form-group-half">
                    <label for="approved-by">Approved by</label>
                    <input type="text" id="approved-by">
                </div>
                <div class="form-group-half">
                    <label for="date">Date/Time</label>
                    <input type="date" id="date">
                </div>
            </div>

            <div class="form-group" style="display: flex; justify-content: space-between; gap: 10px; padding: 0 10px;">
                <div class="form-group-inline" style="flex: 1; margin: 0 10px;">
                    <label for="Type">Type</label>
                    <input type="text" id="Type" style="width: 100%;">
                </div>
                <div class="form-group-inline" style="flex: 1; margin: 0 10px;">
                    <label for="Brand">Brand</label>
                    <input type="text" id="Brand" style="width: 100%;">
                </div>
                <div class="form-group-inline" style="flex: 1; margin: 0 10px;">
                    <label for="Model">Model</label>
                    <input type="text" id="Model" style="width: 100%;">
                </div>
            </div>

            <div class="form-group" style="display: flex; justify-content: space-between; gap: 10px; padding: 0 10px;">
                <div class="form-group-inline" style="flex: 1; margin: 0 10px;">
                    <label for="Series">Series</label>
                    <input type="text" id="Series" style="width: 100%;">
                </div>
                <div class="form-group-inline" style="flex: 1; margin: 0 10px;">
                    <label for="Sign">Sign</label>
                    <input type="text" id="Sign" style="width: 100%;">
                </div>
                <div class="form-group-inline" style="flex: 1; margin: 0 10px;">
                    <label for="date">Date</label>
                    <input type="date" id="date" style="width: 100%; box-sizing: border-box;">
                </div>
            </div>


            <!-- DESCRIPTION OF WORK DONE -->
            <div class="form-group">
                <label for="description">Description of Work Done</label>
                <textarea id="description"></textarea>
            </div>
            <!-- Support by -->
            <div class="form-group">
                <div class="form-group-half">
                    <label for="support-by">Support by</label>
                    <input type="text" id="sSupport-by">
                </div>
                <div class="form-group-half">
                    <label for="date">Date/time</label>
                    <input type="date" id="date">
                </div>
            </div>
            <!-- Accepted by -->
            <div class="form-group">
                <div class="form-group-half">
                    <label for="accepted-by">Accepted by</label>
                    <input type="text" id="accepted-by">
                </div>
                <div class="form-group-half">
                    <label for="date" class="light-text">Date/Time</label>
                    <input type="date" id="date">
                </div>
            </div>
            <!-- Submit Button -->
            <div class="form-footer">
                <button type="submit">Submit</button>
            </div>
        </form>
    </div>
    <script src="../js/itrequest.js"></script>
</body>

</html>