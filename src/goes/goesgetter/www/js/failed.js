/**
 * Created by rusty on 3/7/16.
 */

var FilesFailed = React.createClass({
    render: function() {
        return (
            <div className="filesFailed">
                These are the failed files!
            </div>
        );
    }
});

ReactDOM.render(
    <FilesFailed />,
    document.getElementById('files-failed')
);