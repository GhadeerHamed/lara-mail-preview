<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Mail Class</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        select, .input-field {
            height: 40px;
            padding: 10px;
            margin: 10px;
        }
        .container {
            background-color: aliceblue;
        }
    </style>
</head>
<body class="bg-gray-100 p-6">
<div class="container max-w-lg mx-auto bg-white shadow-lg rounded-lg p-6">
    <h1 class="text-xl font-semibold mb-4">Select a Mail Class</h1>
    <div class="mb-4">
        <label for="mailClass" class="block text-gray-700">Mail Class:</label>
        <select name="mailClass" id="mailClass"
                class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <option value="-" selected>Select a mail class</option>
            @foreach($mailables as $mailClass)
                <option value="{{ $mailClass['class'] }}">{{ $mailClass['name'] }}</option>
            @endforeach
        </select>
    </div>

    <div id="classParameters" class="mt-4 hidden">
        <h2 class="text-lg font-semibold">Parameters:</h2>
        <div id="parametersContent" class="mt-2">
            <!-- Parameters will be displayed here -->
        </div>
        <!-- Submit Button -->
        <div class="flex justify-end mt-4">
            <button id="submitForm" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Submit</button>
        </div>
    </div>
</div>

<div style="margin: 20px 10px; background-color: aliceblue; align-content: center; align-items: center; text-align: center;border-radius: 2px">
    <h2 style="margin: 2rem"> Result: </h2>
    <div id="result" style="max-width: 800px; margin: auto"></div>
</div>
<script>
    $(document).ready(function () {
        // When mail class is selected, fetch parameters
        $('#mailClass').change(function () {
            let selectedClass = $(this).val();
            if (selectedClass && selectedClass !== '-') {
                $.ajax({
                    url: "{{ route('email.preview.submit') }}", // Adjust this route as needed
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        mailClass: selectedClass
                    },
                    success: function (response) {
                        const res = response.data;
                        let paramHtml = '';

                        // Iterate over parameters and generate form fields
                        Object.keys(res).forEach((param) => {
                            const paramObject = res[param];
                            const paramName = paramObject.name;
                            const paramType = paramObject.type?.name;
                            const paramOptions = paramObject.type?.options;
                            const allowNull = paramObject.allow_null || false;
                            const defaultValue = paramObject.default;

                            if (paramOptions && typeof paramOptions === "object") {
                                // Create select input for parameters with options
                                paramHtml += `<label for="${paramName}" class="block text-gray-700">${paramName}:</label>`;
                                paramHtml += `<select name="${paramName}" id="${paramName}" class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 input-field" ${allowNull ? '' : 'required'}>`;
                                Object.keys(paramOptions).map((key) => {
                                    paramHtml += `<option value="${key}" ${key === defaultValue ? 'selected' : ''}>${paramOptions[key]}</option>`;
                                });
                                paramHtml += `</select><br>`;
                            } else {
                                // Create text input for other parameters
                                paramHtml += `<label for="${paramName}" class="block text-gray-700">${paramName}:</label>`;
                                paramHtml += `<input type="text" name="${paramName}" id="${paramName}" value="${defaultValue ?? ''}" class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 input-field" ${allowNull ? '' : 'required'}><br>`;
                            }
                        });

                        // Show parameters section and append inputs
                        $('#classParameters').removeClass('hidden');
                        $('#parametersContent').html(paramHtml);
                    },
                    error: function (xhr, status, error) {
                        $('#classParameters').addClass('hidden');
                        $('#parametersContent').empty();
                    }
                });
            } else {
                $('#classParameters').addClass('hidden');
                $('#parametersContent').empty();
            }
        });

        // Submit form via AJAX when submit button is clicked
        $('#submitForm').click(function (e) {
            e.preventDefault(); // Prevent the default form submission
            $('#result').html('')
            // Collect form data
            let formData = {
                _token: "{{ csrf_token() }}",
                mailClass: $('#mailClass').val(),
            };

            // Collect all inputs and their values
            $('#parametersContent input, #parametersContent select').each(function () {
                formData[$(this).attr('name')] = $(this).val();
            });

            // Send data via AJAX to a route
            $.ajax({
                url: "{{ route('email.preview.render') }}", // Adjust this route for form submission
                method: "POST",
                data: formData,
                success: function (response) {
                    const data = response.data
                    $('#result').html(data)
                },
                error: function (xhr, status, error) {
                    alert('Error submitting form.');
                    // Optionally handle form errors here
                }
            });
        });
    });
</script>
</body>
</html>
