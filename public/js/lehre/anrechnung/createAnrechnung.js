$(function () {

    // Disable all form fields by default
    createAnrechnung.disableFormFields();

    // Create Anrechnung on form submit
    $('#createAnrechnung-submit').click(function(e){

        // Avoid form redirecting automatically
        e.preventDefault();

        // Get form data
        let formData = new FormData($('#createAnrechnung-form')[0]);

        $.ajax({
            url : "CreateAnrechnung/create",
            type: "POST",
            data : formData,
            processData: false, // needed to pass uploaded file with FormData
            contentType: false, // needed to pass uploaded file with FormData
            success:function(data, textStatus, jqXHR){

                if (FHC_AjaxClient.isError(data))
                {
                    FHC_DialogLib.alertWarning(FHC_AjaxClient.getError(data));
                }

                if (FHC_AjaxClient.hasData(data))
                {
                   FHC_DialogLib.alertSuccess(FHC_AjaxClient.getData(data));
                }
            },
            error: function(jqXHR, textStatus, errorThrown){

                FHC_DialogLib.alertWarning(FHC_PhrasesLib.t("ui", "systemfehler"));

            }
        });
    });

})

// TABULATOR FUNCTIONS
// ---------------------------------------------------------------------------------------------------------------------
/**
 * Set form fields and populate selectmenu with LVs of student
 * on row selection.
 *
 * @param row
 */
function func_rowSelected(row){
    let studiensemester_kurzbz = $('#studiensemester_kurzbz').val();
    let prestudent_id = row.getData().prestudent_id;
    let vorname = row.getData().vorname;
    let nachname = row.getData().nachname;

    // Set hidden form field Prestudent ID
    $('#prestudent_id').val(prestudent_id);

    // Set field StudentIn
    $('#student').text(vorname + ' ' + nachname);

    // Populate Select with LVs of student
    createAnrechnung.populateSelectWithStudentLVs(prestudent_id, studiensemester_kurzbz);

    // Enable all form fields
    createAnrechnung.enableFormFields();
}

/**
 * Empty and disable form fields
 * when none row selected.
 *
 * @param data
 * @param rows
 */
function func_rowSelectionChanged(data, rows){

    // If no student is selected in the table
    if (rows.length == 0)
    {
        // ...empty form fields
        createAnrechnung.emptyFormFields();

        // ...disable form fields
        createAnrechnung.disableFormFields();
    }
}

// ---------------------------------------------------------------------------------------------------------------------

var createAnrechnung = {
    emptyFormFields: function(){

        // Empty field StudentIn
        $('#student').text('');

        // Empty form fields (except hidden ones)
        $('#createAnrechnung-form :input:not([type=hidden])').val('');
    },
    disableFormFields: function(){
        let prestudent_id = $('#prestudent_id').data('prestudent_id');

        if (prestudent_id == '')
        {
            // Disable all form elements
            $("#createAnrechnung-form :input").prop("disabled", true);
        }
    },
    enableFormFields: function(){
        $("#createAnrechnung-form :input").prop("disabled", false);
    },
    populateSelectWithStudentLVs: function(prestudent_id, studiensemester_kurzbz){
        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/getLVsByStudent",
            {'prestudent_id': prestudent_id, 'studiensemester_kurzbz': studiensemester_kurzbz},
            {
                successCallback: function (data, textStatus, jqXHR)
                {
                    if (FHC_AjaxClient.isError(data))
                    {
                        // Print error message
                        FHC_DialogLib.alertWarning(FHC_AjaxClient.getError(data));
                    }

                    if (FHC_AjaxClient.hasData(data))
                    {
                        let lehrveranstaltungen = FHC_AjaxClient.getData(data);

                        // Remove previous Lehrveranstaltungen
                        $('#select-lehrveranstaltung option').slice(1).remove();    // Leave first option 'Bitte wählen'

                        // Append Lehrveranstaltungen
                        for (let lv of lehrveranstaltungen){
                            $('#select-lehrveranstaltung').append('<option value="'+ lv.lehrveranstaltung_id +'">'+ lv.bezeichnung +'</option>');
                        }
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    }
}