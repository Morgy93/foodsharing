const fs = require('fs')
const path = require('path')
const build_dir = process.argv[2]
const usage_file = process.argv[3]
const module_file = process.argv[4]

console.log("Extract table usage from php usage file")
console.log("---------------------------------------")
console.log("Build dir" + build_dir)
console.log("DB usage results directory" + usage_file)
console.log("Module usage file " + module_file)

// Read schema sql dump file
const input_data = fs.readFileSync( usage_file, {encoding:'utf8', flag:'r'});
// Remove comments from SQL Commands
var founded_usages = input_data.split('--\n');
let clean_sources = []
founded_usages.forEach((usage) => {
    const filename_regex = /((?<path>[a-z\/]+.[a-z]+)[:-](?<first_line>[0-9]+))[:-]/mi
    const cleanup_regex = /((\n)*([a-z\/]+.[a-z]+)([:-][0-9]+[:-]))(\t)*/gmi

    let filename = usage.match(filename_regex).groups
    clean_sources.push({"path": filename.path, "firstLine": filename.first_line, "code": usage.replace(cleanup_regex, " ")})
})

// Backup json tables
try {
    fs.writeFileSync(path.join(build_dir, 'modules_code_segments.json'), JSON.stringify(clean_sources, null, 2));
} catch (err) {
    console.error(err);
    return;
}
module_map = {}

function decodeTablesFromCode(group, table_types) {
    const code = group.code
    // Request type and name extractor
    // Return names
    const insert_element = ["into", "INTO"]
    const select_element = [ "from", "FROM", ",", "join", "JOIN", "haveInDatabase","grabColumnFromDatabase"]
    const REGEX_REQUEST_IDENTIFIERS = /((?<key>CONCAT|DELETE|UPDATE|REPLACE|INSERT|update|Join|insertOrUpdate|insertMultiple|exists|count|from|fetchAllValuesByCriteria|searchTable|fetchAllByCriteria|haveInDatabase|grabColumnFromDatabase|fetchValueByCriteria|fetchByCriteria|insert|insertIgnore|delete|insertOrUpdateMultiple|requireExists)*[\s\t\n]*(?<location>FROM|INTO|JOIN|\(|,(?= fs))*[\s\t\n\\n\\t]*[\'\"\`]*(?<table>fs_[a-zA-Z0-9\_]+)[\'\"\`]*[\s\t\n]*(SET)*)/gmi// /((?<key>haveInDatabase|grabColumnFromDatabase|DELETE|UPDATE|REPLACE|INSERT|update|insertOrUpdate|insertMultiple|exists|count|from|fetchAllValuesByCriteria|fetchAllByCriteria|fetchValueByCriteria|fetchByCriteria|insert|insertIgnore|delete|insertOrUpdateMultiple|requireExists)*[\s\t\n]*(?<location>FROM|INTO|JOIN|\(|, fs)*[\s\t\n\\n\\t]*[\'\"\`]*(?<table>fs_[a-zA-Z0-9\_]+)[\'\"\`]*[\s\t\n]*(SET)*)/gmi
    let working_table = table_types
    while ((m = REGEX_REQUEST_IDENTIFIERS.exec(code)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === REGEX_REQUEST_IDENTIFIERS.lastIndex) {
            REGEX_REQUEST_IDENTIFIERS.lastIndex++;
        }

        const groups = m.groups
        let type = ""
        if(groups.key || groups.location) {
            if(working_table == null) {
                working_table = {}
            }
            if(!groups.key) {
                switch(groups.location) {
                    case "into":
                    case "INTO":
                        groups.key = "INSERT"
                        break;     
                    case "from":
                    case "FROM":
                    case ",":
                    case "join":
                    case "JOIN":    
                    case "haveInDatabase":
                    case "grabColumnFromDatabase":
                        groups.key = "SELECT"
                        break;
                }
            }
            switch(groups.key) {
                case "SELECT":
                case "fetchAllValuesByCriteria":
                case "fetchAllByCriteria":
                case "fetchValueByCriteria":
                case "fetchByCriteria":
                case "Join":
                case "JOIN":
                case "searchTable":
                case "FROM":
                case "from":
                case "exists":
                case "requireExists":
                case "COUNT":
                case "count":
                case "haveInDatabase":
                case "grabColumnFromDatabase":
                    type = "SELECT"
                    break;
                case "DELETE":
                case "delete":
                    type = "DELETE"
                    break;
                case "UPDATE":
                case "update":
                case "REPLACE":
                    type = "UPDATE"
                    break;
                case "INSERT":
                case "insert":
                case "insertIgnore":
                case "insertOrUpdate":
                case "insertMultiple":
                case "insertOrUpdateMultiple":
                    type = "INSERT"
                    break;
            }
            const descriptor = {'key':groups.key, 'location': groups.location, 'type': type, source: group}
            if(!(groups.table in working_table)) {
                working_table[groups.table] = []
            }
            if(!working_table[groups.table].includes(descriptor)) {
                working_table[groups.table].push(descriptor)
            }
        }
    }

    return working_table
}

function decodeModuleFromPath(path) {
    const REGEX_MODULE_NAME = /(?<Module>[a-zA-Z0-9]+)\/[^\/]*\.php/gmi
    if((matches = REGEX_MODULE_NAME.exec(path)) !== null) {
        return matches.groups.Module
    } {
        return "Unclassified"
    }
}

// Extract files
clean_sources.map((group) => {
    let tables;
    { 
        const module_name = decodeModuleFromPath(group.path)
        if(module_name in module_map) {
            tables = module_map[module_name]
        } else {
            tables = null
        }
        tables = decodeTablesFromCode(group, tables)
        if(tables) {
            module_map[module_name] = tables
        }
    }
})

// Backup json tables
try {
    fs.writeFileSync(module_file, JSON.stringify(module_map, null, 2));
} catch (err) {
    console.error(err);
    return;
}
