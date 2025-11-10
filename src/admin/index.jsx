import React, { useState, useEffect } from 'react';
import { createRoot } from '@wordpress/element';

const DynamicContentBuilder = () => {
    const [conditions, setConditions] = useState([]);
    const [contentVariants, setContentVariants] = useState([{ id: 1, content: '', conditions: [] }]);
    const [defaultContent, setDefaultContent] = useState('');
    const [conditionOperator, setConditionOperator] = useState('AND');

    const conditionTypes = {
        user: ['role', 'logged_in', 'user_meta'],
        location: ['country', 'city', 'ip_address'],
        device: ['device_type', 'browser', 'os'],
        time: ['date_range', 'day_of_week', 'time_range'],
        page: ['page_type', 'url_parameter', 'referrer'],
        woocommerce: ['cart_total', 'cart_items', 'purchased_product'],
        advanced: ['cookie', 'session', 'custom_code', 'ab_test']
    };

    const addCondition = (variantId) => {
        const newCondition = {
            id: Date.now(),
            type: 'role',
            operator: 'equals',
            value: ''
        };

        setContentVariants(prev => 
            prev.map(variant => 
                variant.id === variantId 
                    ? { ...variant, conditions: [...variant.conditions, newCondition] }
                    : variant
            )
        );
    };

    const updateCondition = (variantId, conditionId, field, value) => {
        setContentVariants(prev =>
            prev.map(variant => 
                variant.id === variantId
                    ? {
                        ...variant,
                        conditions: variant.conditions.map(cond =>
                            cond.id === conditionId ? { ...cond, [field]: value } : cond
                        )
                    }
                    : variant
            )
        );
    };

    const removeCondition = (variantId, conditionId) => {
        setContentVariants(prev =>
            prev.map(variant =>
                variant.id === variantId
                    ? {
                        ...variant,
                        conditions: variant.conditions.filter(c => c.id !== conditionId)
                    }
                    : variant
            )
        );
    };

    const addVariant = () => {
        const newVariant = {
            id: Date.now(),
            content: '',
            conditions: []
        };
        setContentVariants([...contentVariants, newVariant]);
    };

    const removeVariant = (variantId) => {
        setContentVariants(contentVariants.filter(v => v.id !== variantId));
    };

    const updateVariantContent = (variantId, content) => {
        setContentVariants(prev =>
            prev.map(variant =>
                variant.id === variantId ? { ...variant, content } : variant
            )
        );
    };

    const saveContent = async () => {
        const data = {
                variants: JSON.stringify(contentVariants), // ✅ key change

            defaultContent,
            conditionOperator,
            action: 'ccp_save_content',
            nonce: window.ccpAdmin.nonce,
            post_id: window.ccpAdmin.postId
        };

        try {
            const response = await fetch(window.ccpAdmin.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(data)
            });

            const result = await response.json();
            if (result.success) {
                alert('Content saved successfully!');
            }
        } catch (error) {
            console.error('Save error:', error);
            alert('Error saving content');
        }
    };

    const renderConditionFields = (condition, variantId) => {
        const { type, operator, value } = condition;

        return (
            <div key={condition.id} className="ccp-condition-row">
                <select
                    value={type}
                    onChange={(e) => updateCondition(variantId, condition.id, 'type', e.target.value)}
                    className="ccp-select"
                >
                    {Object.entries(conditionTypes).map(([group, types]) => (
                        <optgroup key={group} label={group.toUpperCase()}>
                            {types.map(t => (
                                <option key={t} value={t}>{t.replace(/_/g, ' ').toUpperCase()}</option>
                            ))}
                        </optgroup>
                    ))}
                </select>

                <select
                    value={operator}
                    onChange={(e) => updateCondition(variantId, condition.id, 'operator', e.target.value)}
                    className="ccp-select"
                >
                    <option value="equals">Equals</option>
                    <option value="not_equals">Not Equals</option>
                    <option value="contains">Contains</option>
                    <option value="greater_than">Greater Than</option>
                    <option value="less_than">Less Than</option>
                </select>

                {renderValueInput(condition, variantId)}

                <button
                    onClick={() => removeCondition(variantId, condition.id)}
                    className="ccp-btn-remove"
                >
                    Remove
                </button>
            </div>
        );
    };

    const renderValueInput = (condition, variantId) => {
        const { type, value } = condition;

        // Special inputs for specific condition types
        if (type === 'role') {
            return (
                <select
                    value={value}
                    onChange={(e) => updateCondition(variantId, condition.id, 'value', e.target.value)}
                    className="ccp-select"
                >
                    <option value="">Select Role</option>
                    <option value="administrator">Administrator</option>
                    <option value="editor">Editor</option>
                    <option value="author">Author</option>
                    <option value="subscriber">Subscriber</option>
                </select>
            );
        }

        if (type === 'logged_in') {
            return (
                <select
                    value={value}
                    onChange={(e) => updateCondition(variantId, condition.id, 'value', e.target.value)}
                    className="ccp-select"
                >
                    <option value="logged_in">Logged In</option>
                    <option value="logged_out">Logged Out</option>
                </select>
            );
        }

        if (type === 'device_type') {
            return (
                <select
                    value={value}
                    onChange={(e) => updateCondition(variantId, condition.id, 'value', e.target.value)}
                    className="ccp-select"
                >
                    <option value="desktop">Desktop</option>
                    <option value="mobile">Mobile</option>
                    <option value="tablet">Tablet</option>
                </select>
            );
        }

        if (type === 'date_range') {
            return (
                <div className="ccp-date-range">
                    <input
                        type="date"
                        value={value.start_date || ''}
                        onChange={(e) => updateCondition(variantId, condition.id, 'value', {
                            ...value,
                            start_date: e.target.value
                        })}
                        className="ccp-input"
                    />
                    <span>to</span>
                    <input
                        type="date"
                        value={value.end_date || ''}
                        onChange={(e) => updateCondition(variantId, condition.id, 'value', {
                            ...value,
                            end_date: e.target.value
                        })}
                        className="ccp-input"
                    />
                </div>
            );
        }

        // Default text input
        return (
            <input
                type="text"
                value={value}
                onChange={(e) => updateCondition(variantId, condition.id, 'value', e.target.value)}
                className="ccp-input"
                placeholder="Enter value"
            />
        );
    };

    return (
        <div className="ccp-builder">
            <div className="ccp-header">
                <h2>Dynamic Content Builder</h2>
                <div className="ccp-operator-select">
                    <label>Condition Logic:</label>
                    <select
                        value={conditionOperator}
                        onChange={(e) => setConditionOperator(e.target.value)}
                        className="ccp-select"
                    >
                        <option value="AND">Match ALL conditions (AND)</option>
                        <option value="OR">Match ANY condition (OR)</option>
                    </select>
                </div>
            </div>

            <div className="ccp-variants">
                {contentVariants.map((variant, index) => (
                    <div key={variant.id} className="ccp-variant-card">
                        <div className="ccp-variant-header">
                            <h3>Content Variant #{index + 1}</h3>
                            {contentVariants.length > 1 && (
                                <button
                                    onClick={() => removeVariant(variant.id)}
                                    className="ccp-btn-remove"
                                >
                                    Remove Variant
                                </button>
                            )}
                        </div>

                        <div className="ccp-conditions-section">
                            <h4>Display Conditions</h4>
                            {variant.conditions.map(condition =>
                                renderConditionFields(condition, variant.id)
                            )}
                            <button
                                onClick={() => addCondition(variant.id)}
                                className="ccp-btn-add"
                            >
                                + Add Condition
                            </button>
                        </div>

                        <div className="ccp-content-section">
                            <h4>Content to Display</h4>
                            <textarea
                                value={variant.content}
                                onChange={(e) => updateVariantContent(variant.id, e.target.value)}
                                className="ccp-textarea"
                                placeholder="Enter content or shortcode..."
                                rows="6"
                            />
                        </div>
                    </div>
                ))}
            </div>

            <button onClick={addVariant} className="ccp-btn-add-variant">
                + Add New Variant
            </button>

            <div className="ccp-default-content">
                <h3>Default Content</h3>
                <p className="ccp-help-text">Shown when no conditions match</p>
                <textarea
                    value={defaultContent}
                    onChange={(e) => setDefaultContent(e.target.value)}
                    className="ccp-textarea"
                    placeholder="Enter default content..."
                    rows="6"
                />
            </div>

            <div className="ccp-actions">
                <button onClick={saveContent} className="ccp-btn-save">
                    Save Dynamic Content
                </button>
            </div>

            <style>{`
                .ccp-builder {
                    padding: 20px;
                    max-width: 1200px;
                    margin: 0 auto;
                }
                
                .ccp-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 30px;
                    padding-bottom: 20px;
                    border-bottom: 2px solid #e5e7eb;
                }
                
                .ccp-operator-select {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                
                .ccp-variants {
                    display: flex;
                    flex-direction: column;
                    gap: 20px;
                    margin-bottom: 20px;
                }
                
                .ccp-variant-card {
                    background: white;
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                    padding: 20px;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                }
                
                .ccp-variant-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 20px;
                }
                
                .ccp-conditions-section {
                    margin-bottom: 20px;
                    padding: 15px;
                    background: #f9fafb;
                    border-radius: 6px;
                }
                
                .ccp-condition-row {
                    display: flex;
                    gap: 10px;
                    margin-bottom: 10px;
                    align-items: center;
                }
                
                .ccp-select, .ccp-input {
                    padding: 8px 12px;
                    border: 1px solid #d1d5db;
                    border-radius: 4px;
                    font-size: 14px;
                }
                
                .ccp-select {
                    min-width: 150px;
                }
                
                .ccp-input {
                    flex: 1;
                }
                
                .ccp-textarea {
                    width: 100%;
                    padding: 12px;
                    border: 1px solid #d1d5db;
                    border-radius: 4px;
                    font-size: 14px;
                    font-family: monospace;
                    resize: vertical;
                }
                
                .ccp-btn-add, .ccp-btn-add-variant, .ccp-btn-save {
                    padding: 10px 20px;
                    background: #2563eb;
                    color: white;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    font-weight: 500;
                }
                
                .ccp-btn-add:hover, .ccp-btn-add-variant:hover, .ccp-btn-save:hover {
                    background: #1d4ed8;
                }
                
                .ccp-btn-remove {
                    padding: 8px 16px;
                    background: #dc2626;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                }
                
                .ccp-btn-remove:hover {
                    background: #b91c1c;
                }
                
                .ccp-default-content {
                    margin-top: 30px;
                    padding: 20px;
                    background: white;
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                }
                
                .ccp-help-text {
                    color: #6b7280;
                    font-size: 14px;
                    margin-bottom: 10px;
                }
                
                .ccp-actions {
                    margin-top: 30px;
                    text-align: center;
                }
                
                .ccp-btn-save {
                    font-size: 16px;
                    padding: 12px 32px;
                }
                
                .ccp-date-range {
                    display: flex;
                    gap: 10px;
                    align-items: center;
                    flex: 1;
                }
            `}</style>
        </div>
    );
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('ccp-react-root');
    if (container) {
        const root = createRoot(container);
        root.render(<DynamicContentBuilder />);
    }
});

export default DynamicContentBuilder;