const CheckBox = (props) => {
    return (
        <>
            <div className={props.wrapperClass}>
                <input
                    className=  {props.inputClass}
                    id=         {props.id}
                    key=        {props.key}
                    type=       {props.type || 'checkbox'}
                    name=       {props.name || 'basic-input'}
                    value=      {props.value}
                    checked=    {props.checked}
                    onChange=   {(e) => { props.onChange?.(e) }}
                    onClick=    {(e) => { props.onClick?.(e) }}
                    onMouseOver={(e) => { props.onMouseOver?.(e) }}
                    onMouseOut= {(e) => { props.onMouseOut?.(e) }}
                />
                {
                    props.description &&
                    <p className={props.descClass}>
                        {props.description}
                    </p>
                }
            </div>
        </>
    );
}

export default CheckBox;