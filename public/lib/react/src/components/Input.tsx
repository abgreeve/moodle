import React from "react";

export interface InputProps
  extends Omit<
    React.InputHTMLAttributes<HTMLInputElement>,
    "type" | "onChange"
  > {
  label?: string;
  type?: string;
  onChange?: (event: React.ChangeEvent<HTMLInputElement>) => void;
}

/**
 * Simple input component for use with the Mustache React helper.
 */
export default function Input(props: InputProps): React.ReactElement {
  const {
    id,
    name,
    type = "text",
    value,
    defaultValue,
    placeholder,
    disabled = false,
    readOnly = false,
    required = false,
    label,
    className = "",
    onChange,
    ...rest
  } = props;

  // Spread all remaining attributes directly onto the <input /> element.
  const domProps = rest;

  // If no explicit id is given, fall back to "name" (same as native behavior).
  const finalId = id || name || undefined;

  // JSX checks this many times so we simplify it here.
  const hasVisibleLabel = Boolean(label && label.trim() !== "");

  const inputElement = (
    <input
      id={finalId}
      name={name}
      type={type}
      value={value}
      defaultValue={defaultValue}
      placeholder={placeholder}
      disabled={disabled}
      readOnly={readOnly}
      required={required}
      onChange={onChange}
      className={className}
      {...domProps}
    />
  );

  // If user did not request a visible label, render <input /> only.
  if (!hasVisibleLabel) {
    return inputElement;
  }

  // Wrap input in a <label> for basic accessibility.
  return (
    <label htmlFor={finalId}>
      <span>{label}</span>
      {inputElement}
    </label>
  );
}
