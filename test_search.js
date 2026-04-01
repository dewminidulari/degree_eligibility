const all_courses = [{"module_code":"ACLT11042","module_name":"Academic Literacy I","credit_value":"2","is_gpa_module":false,"module_status":"Optional"},{"module_code":"CHEM11002","module_name":"Physical Chemistry","credit_value":"2","is_gpa_module":true,"module_status":"Compulsory"}];

const value = "Ace";
const query = String(value).toLowerCase();
const filtered = all_courses.filter(c => {
    const mCode = c.module_code ? String(c.module_code).toLowerCase() : '';
    const mName = c.module_name ? String(c.module_name).toLowerCase() : '';
    return mCode.includes(query) || mName.includes(query);
});

console.log(filtered);
