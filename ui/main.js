//
// This is the main app for the qsl module
//
function qruqsp_qsl_main() {
    //
    // The panel to list the entry
    //
    this.menu = new M.panel('entry', 'qruqsp_qsl_main', 'menu', 'mc', 'medium', 'sectioned', 'qruqsp.qsl.main.menu');
    this.menu.data = {};
    this.menu.nplist = [];
    this.menu.sections = {
        'search':{'label':'', 'type':'livesearchgrid', 'livesearchcols':4,
            'headerValues':['Time', 'Frequency', 'From', 'To'],
            'cellClasses':['multiline', 'multiline', 'multiline', 'multiline'],
            'hint':'Search entry',
            'noData':'No entry found',
            },
        'entries':{'label':'Log Entries', 'type':'simplegrid', 'num_cols':4,
            'noData':'No entry',
            'headerValues':['UTC Time', 'Frequency', 'From', 'To'],
            'cellClasses':['multiline', 'multiline', 'multiline', 'multiline'],
            'addTxt':'Add Log Entry',
            'addFn':'M.qruqsp_qsl_main.edit.open(\'M.qruqsp_qsl_main.menu.open();\',0,null);'
            },
    }
    this.menu.liveSearchCb = function(s, i, v) {
        if( s == 'search' && v != '' ) {
            M.api.getJSONBgCb('qruqsp.qsl.entrySearch', {'tnid':M.curTenantID, 'start_needle':v, 'limit':'25'}, function(rsp) {
                M.qruqsp_qsl_main.menu.liveSearchShow('search',null,M.gE(M.qruqsp_qsl_main.menu.panelUID + '_' + s), rsp.entries);
                });
        }
    }
    this.menu.liveSearchResultValue = function(s, f, i, j, d) {
        return this.cellValue(s, i, j, d);
    }
    this.menu.liveSearchResultRowFn = function(s, f, i, j, d) {
        return this.rowFn(s, i, d);
    }
    this.menu.cellValue = function(s, i, j, d) {
        if( s == 'entries' || s == 'search' ) {
            switch(j) {
                case 0: return '<span class="maintext">' + d.time_of_traffic + '</span><span class="subtext">' + d.date_of_traffic + '</span>';
                case 1: return '<span class="maintext">' + d.frequency + '</span><span class="subtext">' + d.mode_text + '</span>';
                case 2: return '<span class="maintext">' + d.from_call + '</span><span class="subtext">' + d.from_rst + '</span>';
                case 3: return '<span class="maintext">' + d.to_call + '</span><span class="subtext">' + d.to_rst + '</span>';
            }
        }
    }
    this.menu.rowFn = function(s, i, d) {
        if( s == 'entries' || s == 'search' ) {
            return 'M.qruqsp_qsl_main.edit.open(\'M.qruqsp_qsl_main.menu.open();\',\'' + d.id + '\',M.qruqsp_qsl_main.entry.nplist);';
        }
    }
    this.menu.open = function(cb) {
        M.api.getJSONCb('qruqsp.qsl.entryList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_qsl_main.menu;
            p.data = rsp;
            p.nplist = (rsp.nplist != null ? rsp.nplist : null);
            p.refresh();
            p.show(cb);
        });
    }
    this.menu.addClose('Back');

    //
    // The panel to display Log Entry
    //
    this.entry = new M.panel('Log Entry', 'qruqsp_qsl_main', 'entry', 'mc', 'medium mediumaside', 'sectioned', 'qruqsp.qsl.main.entry');
    this.entry.data = null;
    this.entry.entry_id = 0;
    this.entry.sections = {
    }
    this.entry.open = function(cb, eid, list) {
        if( eid != null ) { this.entry_id = eid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('qruqsp.qsl.entryGet', {'tnid':M.curTenantID, 'entry_id':this.entry_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_qsl_main.entry;
            p.data = rsp.entry;
            p.refresh();
            p.show(cb);
        });
    }
    this.entry.addButton('edit', 'Edit', 'M.qruqsp_qsl_main.edit.open(\'M.qruqsp_qsl_main.entry.open();\',M.qruqsp_qsl_main.entry.entry_id);');
    this.entry.addClose('Back');

    //
    // The panel to edit Log Entry
    //
    this.edit = new M.panel('Log Entry', 'qruqsp_qsl_main', 'edit', 'mc', 'medium mediumaside', 'sectioned', 'qruqsp.qsl.main.edit');
    this.edit.data = null;
    this.edit.entry_id = 0;
    this.edit.nplist = [];
    this.edit.sections = {
        'general':{'label':'', 'aside':'yes','fields':{
            'date_of_traffic':{'label':'UTC Date', 'type':'date'},
            'time_of_traffic':{'label':'UTC Time', 'type':'text','size':'small'},
            'frequency':{'label':'Frequency', 'type':'text'},
            'mode':{'label':'Mode', 'type':'toggle','toggles':{'0':'?','20':'LSB','30':'USB','40':'FM','10':'CW','50':'RTTY','60':'PSK','70':'JT', '100':'AM'}},
            }},
        'calls':{'label':'', 'aside':'yes','fields':{
            'from_call':{'label':'Call Sign From', 'type':'text'},
            'to_call':{'label':'Call Sign To', 'type':'text'},
            }},
        'rsts':{'label':'', 'aside':'yes','fields':{
            'from_r':{'label':'Readability From', 'type':'toggle','toggles':{'0':'?','1':'1','2':'2','3':'3','4':'4','5':'5'}},
            'from_s':{'label':'Strength From', 'type':'toggle','toggles':{'0':'?','1':'1','2':'2','3':'3','4':'4','5':'5','6':'6','7':'7','8':'8','9':'9'}},
            'from_t':{'label':'Tone From', 'type':'toggle','toggles':{'0':'?','1':'1','2':'2','3':'3','4':'4','5':'5','6':'6','7':'7','8':'8','9':'9'}},
            'to_r':{'label':'Readability To', 'type':'toggle','toggles':{'0':'?','1':'1','2':'2','3':'3','4':'4','5':'5'}},
            'to_s':{'label':'Strength To', 'type':'toggle','toggles':{'0':'?','1':'1','2':'2','3':'3','4':'4','5':'5','6':'6','7':'7','8':'8','9':'9'}},
            'to_t':{'label':'Tone To', 'type':'toggle','toggles':{'0':'?','1':'1','2':'2','3':'3','4':'4','5':'5','6':'6','7':'7','8':'8','9':'9'}},
            }},
        '_traffic':{'label':'Traffic', 'fields':{
            'traffic':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.qruqsp_qsl_main.edit.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.qruqsp_qsl_main.edit.entry_id > 0 ? 'yes' : 'no'; },
                'fn':'M.qruqsp_qsl_main.edit.remove();'},
            }},
        };
    this.edit.fieldValue = function(s, i, d) { return this.data[i]; }
    this.edit.fieldHistoryArgs = function(s, i) {
        return {'method':'qruqsp.qsl.entryHistory', 'args':{'tnid':M.curTenantID, 'entry_id':this.entry_id, 'field':i}};
    }
    this.edit.open = function(cb, eid, list) {
        if( eid != null ) { this.entry_id = eid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('qruqsp.qsl.entryGet', {'tnid':M.curTenantID, 'entry_id':this.entry_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_qsl_main.edit;
            p.data = rsp.entry;
            p.refresh();
            p.show(cb);
        });
    }
    this.edit.save = function(cb) {
        if( cb == null ) { cb = 'M.qruqsp_qsl_main.edit.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.entry_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('qruqsp.qsl.entryUpdate', {'tnid':M.curTenantID, 'entry_id':this.entry_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            console.log(c);
            M.api.postJSONCb('qruqsp.qsl.entryAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.qruqsp_qsl_main.edit.entry_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.edit.remove = function() {
        if( confirm('Are you sure you want to remove entry?') ) {
            M.api.getJSONCb('qruqsp.qsl.entryDelete', {'tnid':M.curTenantID, 'entry_id':this.entry_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.qruqsp_qsl_main.edit.close();
            });
        }
    }
    this.edit.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.entry_id) < (this.nplist.length - 1) ) {
            return 'M.qruqsp_qsl_main.edit.save(\'M.qruqsp_qsl_main.edit.open(null,' + this.nplist[this.nplist.indexOf('' + this.entry_id) + 1] + ');\');';
        }
        return null;
    }
    this.edit.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.entry_id) > 0 ) {
            return 'M.qruqsp_qsl_main.edit.save(\'M.qruqsp_qsl_main.edit.open(null,' + this.nplist[this.nplist.indexOf('' + this.entry_id) - 1] + ');\');';
        }
        return null;
    }
    this.edit.addButton('save', 'Save', 'M.qruqsp_qsl_main.edit.save();');
    this.edit.addClose('Cancel');
    this.edit.addButton('next', 'Next');
    this.edit.addLeftButton('prev', 'Prev');

    //
    // Start the app
    // cb - The callback to run when the user leaves the main panel in the app.
    // ap - The application prefix.
    // ag - The app arguments.
    //
    this.start = function(cb, ap, ag) {
        args = {};
        if( ag != null ) {
            args = eval(ag);
        }
        
        //
        // Create the app container
        //
        var ac = M.createContainer(ap, 'qruqsp_qsl_main', 'yes');
        if( ac == null ) {
            alert('App Error');
            return false;
        }
        
        this.menu.open(cb);
    }
}
