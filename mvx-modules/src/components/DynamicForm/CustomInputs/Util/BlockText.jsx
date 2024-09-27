const BlockText = (props) => {
    return (
        <>
            <div className={props.wrapperClass}>
                <p className={props.blockTextClass}>
                    {props.value}
                </p>
            </div>
        </>
    );
}

export default BlockText;