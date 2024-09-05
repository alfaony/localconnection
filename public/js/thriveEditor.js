// thriveEditor-init.js

document.addEventListener('DOMContentLoaded', function () 
{
    // Get all textarea elements with the class 'thriveEditor'
    var ckeditorInputs = document.querySelectorAll('input.thriveEditor');

    // Loop through each textarea and initialize CKEditor
    ckeditorInputs.forEach(function (textarea) 
    {
        var id = textarea.getAttribute('data-ids');
        var defaultValue = textarea.getAttribute('value');

        generateThriveEditor(id, defaultValue);
    });
});

function generateThriveEditor(id,defaultValue = null)
{
    // Generate Div
    var editorDiv = document.createElement('div');
    editorDiv.id = 'editor_' + id;
    editorDiv.style.minHeight = '120px';

    // Append the editor div after the description element
    var descriptionElement = document.getElementById('description_' + id);
    descriptionElement.type = 'hidden';
    descriptionElement.parentNode.insertBefore(editorDiv, descriptionElement.nextSibling);

    // Bind Div
    var quill = new Quill('#editor_'+id, {
        theme: 'snow',
        modules: {
            toolbar: 
            [
                    [{ header: [1, 2, 3, 4, 5, 6, false] }],
                    ["bold", "italic"],
                    [{ list: "ordered" }, { list: "bullet" }],
                    [{ color: [] }, { background: [] }],
                    ["link"] // Ensure hyperlink is included
            ]
    },
    });

    if(defaultValue)
    {
        quill.root.innerHTML = defaultValue;
    }
    
    quill.on('text-change', function(delta, oldDelta, source) {
        document.getElementById("description_"+id).value = quill.root.innerHTML;
    });
}